<?php

namespace App\Http\Controllers;

use App\Constant\CouponStatusType;
use App\Constant\FoodType;
use App\Models\User;
use App\Models\Coupon;
use App\Models\Comment;
use App\Services\Api\ImgurHandler;
use App\Http\Requests\StoreCouponRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class CouponController extends Controller
{
    protected $foodArray;

    public function __construct()
    {
        // Note: 解決 Auth 還未於 construct 載入的問題
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();
            $this->collects = $this->user ? $this->user->collect_list : [];
            return $next($request);
        });

        $this->foodArray = array_merge(FoodType::FOOD_COMMON, FoodType::FOOD_LESS, FoodType::FOOD_DRINK);
    }

    /**
     * 頁面 - 最新優惠券
     */
    public function index()
    {
        $list = pCacheDb('c:home', function () {
            return Coupon::where('status', CouponStatusType::USABLE)->get();
        }, false, 3600);

        $pageTitle = "最新優惠券";

        $collects = $this->collects;

        $foodCommon = FoodType::FOOD_COMMON;
        $foodLess   = FoodType::FOOD_LESS;
        $foodDrink  = FoodType::FOOD_DRINK;
        $foodMore   = FoodType::FOOD_MORE;
        $foodHide   = FoodType::FOOD_HIDE;

        return view('coupons.index', compact('list', 'pageTitle', 'collects', 'foodCommon', 'foodLess', 'foodDrink', 'foodMore', 'foodHide'));
    }

    /**
     * 頁面 - 已失效優惠券
     */
    public function expired()
    {
        $list = pCacheDb('c:exp', function () {
            return Coupon::where('status', CouponStatusType::EXPIRED)
                ->orderBy('view_cou', 'desc')
                ->take(30) // 前30筆
                ->get();
        }, false, 86400);

        $pageTitle = "已失效優惠券";

        $collects = $this->collects;

        return view('coupons.index', compact('list', 'collects', 'pageTitle'));
    }

    /**
     * 頁面 - 優惠券內頁
     *
     * @param  string  $slug
     */
    public function show($slug)
    {
        $isHost = (request()->ip() === config('app.ip'));

        // 取得已審核 Coupon (可用/過期)
        $coupon = pCacheDb('c:' . $slug, function () use ($slug) {
            return Coupon::where('slug', $slug)
                ->where(function ($query) {
                    $query->where('status', CouponStatusType::USABLE)
                        ->orWhere('status', CouponStatusType::EXPIRED);
                })
                ->first();
        }, $isHost);

        // 若為未審核 Coupon
        if (!$coupon) {
            // 檢查是否登入
            $user = $this->user;
            if (!$user) {
                return view('coupons.404', compact('slug'));
            }

            // 取得未審核 Coupon
            $coupon = Coupon::where('slug', $slug)
                ->where('status', CouponStatusType::PENDING)
                ->first();
            if (!$coupon) {
                return view('coupons.404', compact('slug'));
            }

            // 檢查是否為 admin 或是 新增者
            $isAdmin = $user->hasTeamRole($user->currentTeam, 'admin');
            if (!$isAdmin && ($coupon->user_id !== $user->id)) {
                return view('coupons.404', compact('slug'));
            }

            return view('coupons.pending', compact('coupon', 'isAdmin'));
        }

        $collects = $this->collects;

        // Coupon留言 - 快取30分鐘
        $comments = pCacheDb('cmt:' . $slug, function () use ($coupon) {
            return $coupon->comments()
                ->orderby('created_at', 'desc')
                ->take(10)
                ->join('users', 'comments.user_id', '=', 'users.id')
                ->select('users.name', 'comments.*')
                ->get();
        }, $isHost, 1800);

        // 討論區留言 - 快取30分鐘
        $commentsCount = $comments->count();
        if ($commentsCount < 5) {
            $chatComment = pCacheDb('cmt:chat', function () {
                return Comment::where('coupon_id', '=', 1)
                    ->orderby('created_at', 'desc')
                    ->take(5)
                    ->join('users', 'comments.user_id', '=', 'users.id')
                    ->select('users.name', 'comments.*')
                    ->get();
            }, $isHost, 1800);
            $comments = $comments->merge($chatComment) ?? [];
        }

        return view('coupons.show', compact('coupon', 'collects', 'comments'));
    }

    /**
     * 頁面 - 新增優惠券
     */
    public function create()
    {
        // 權限判斷
        $user = $this->user;
        if (!$user->hasTeamPermission($user->currentTeam, 'create')) {
            return redirect()->route('coupons.index');
        }

        $foodArray = $this->foodArray;
        return view('coupons.create', compact('foodArray'));
    }

    /**
     * 功能 - 新增優惠券
     *
     * @param  Request  $request
     */
    public function store(StoreCouponRequest $request)
    {
        $slug = $request->input('slug', $request->input('title'));
        if (is_null($slug)) {
            return redirect()->route('coupons.index');
        }

        // 權限判斷
        $user = $this->user;
        if (!$user->hasTeamPermission($user->currentTeam, 'create')) {
            return redirect()->route('coupons.index');
        }

        $result = (object) $this->handle($request, $user, 'store');

        // 全站動態通知
        if ($result->message) {
            $notifyContent = sprintf("Coupon\n\n%s\n\n%s", $result->message, route('coupons.show', $result->slug));
            pSiteLineNotify($notifyContent);
        }

        $message = sprintf('新增成功！ %s  <a class="text-decoration-none" href="%s" target="_blank">查看</a>', $result->message, route('coupons.show', $result->slug));
        if (!$result->success) {
            $message = $result->message;
        }

        return Redirect()->route('coupons.create')->with('message', $message);
    }

    /**
     * 功能 - 計數器 : 前端判斷為真人瀏覽
     *
     * @param  Request  $request
     */
    public function viewCount(Request $request)
    {
        if ($slug = $request->input('slug')) {
            pCacheViewCou($slug);
        }
    }

    /**
     * 頁面 - 修改優惠券 (目前僅role-admin)
     *
     * @param  string  $slug
     */
    public function edit($slug)
    {
        // 權限判斷
        $user = $this->user;
        if (!$user->hasTeamPermission($user->currentTeam, 'update')) {
            return redirect()->route('coupons.index');
        }

        $coupon = Coupon::where('slug', $slug)->first();
        if (!$coupon) {
            return view('coupons.404', compact('slug'));
        }

        $foodArray = $this->foodArray;

        return view('coupons.edit', compact('coupon', 'foodArray'));
    }

    /**
     * 功能 - 修改優惠券 (目前僅role-admin)
     *
     * @param  Request  $request
     */
    public function update(Request $request)
    {
        $slug = $request->input('slug', $request->input('title'));
        if (is_null($slug)) {
            return redirect()->route('coupons.index');
        }

        // 權限判斷
        $user = $this->user;
        if (!$user->hasTeamPermission($user->currentTeam, 'update')) {
            return redirect()->route('coupons.index');
        }

        $result = (object) $this->handle($request, $user, 'update');

        $message = sprintf('更新成功！  <a class="text-decoration-none" href="%s" target="_blank">查看</a>', route('coupons.show', $slug));
        if (!$result->success) {
            $message = $result->message;
        }

        return Redirect()->route('coupons.edit', $result->slug)->with('message', $message);
    }

    /**
     * 功能 - 刪除優惠券 (目前僅role-admin)
     *
     * @param  string  $slug
     */
    public function destroy($slug)
    {
        // 權限判斷
        $user = $this->user;
        if (!$user->hasTeamPermission($user->currentTeam, 'delete')) {
            return redirect()->route('coupons.index');
        }

        $coupon = Coupon::where('slug', $slug)->first();
        if (!$coupon) {
            return back();
        }

        $result = $coupon->delete();
        if (!$result) {
            return back();
        }

        return redirect()->route('coupons.index');
    }

    /**
     * 功能 - 審核優惠券 (目前僅role-admin)
     *
     * @param  int  $id
     * @param  string  $type
     */
    public function verify($id, $type)
    {
        // 權限判斷
        $user = $this->user;
        if (!$user->hasTeamPermission($user->currentTeam, 'verify')) {
            return redirect()->route('coupons.index');
        }

        $coupon = Coupon::where('id', $id)->first();
        if (!$coupon) {
            return back();
        }

        if ($type === 'pass') {
            $coupon->slug   = $coupon->title;
            $coupon->status = CouponStatusType::USABLE;
            $coupon->save();

            // comment新增留言:系統通知
            $postData = [
                'user_id'    => 2,
                'tag'        => 'reply',
                'content'    => '系統通知: 由 ' . $coupon->user->name . ' 協助新增了這張優惠券！',
                'ip'         => '0.0.0.0',
                'created_at' => $coupon->created_at,
            ];
            $coupon->comments()->create($postData);
            pForgetCache(['c:' . $coupon->slug, 'c:home']);
        } elseif ($type === 'fail') {
            $coupon->slug   = $coupon->slug . "-fail";  // Note: 方便DB易讀
            $coupon->status = CouponStatusType::FAIL;
            $coupon->save();
            $coupon->delete();
        } else {
            return "type異常";
        }

        return redirect()->route('coupons.show', $coupon->slug);
    }

    /**
     * 頁面 - 待審核列表 (目前僅role-admin)
     */
    public function pendingList()
    {
        // 權限判斷
        $user = $this->user;
        if (!$user->hasTeamPermission($user->currentTeam, 'verify')) {
            return redirect()->route('coupons.index');
        }

        $list = Coupon::where('status', CouponStatusType::PENDING)->get();

        return view('admin.pending-list', compact('list'));
    }

    /**
     * 處理 新增.修改
     *
     * @param  Request $request
     * @param  User    $User
     * @param  string  $type
     */
    public function handle(Request $request, User $user, $type)
    {
        $slug   = $request->input('slug', $request->input('title'));
        $status = $request->input('status', 0);
        $userId = $user->id;
        $message = "";

        // 處理tag與content
        $tag = [];
        $content = [];

        $tags = $request->input('tag');
        $foodArray = $this->foodArray;
        $foodMore  = FoodType::FOOD_MORE;
        foreach ($tags as $key => $value) {
            if ($value > 0) {
                $tagList[] = $key;

                $tagName = $foodArray[$key][0] . 'x' . $value;
                $tagNameList[] = $tagName;

                // 組合餐
                foreach ($foodMore as $key2 => $value2) {
                    if ($tagName === $value2[0]) {
                        $tagList[] = $key2;
                    }
                }
            }
        }
        $tag = implode(' ', $tagList);
        $content = implode(' + ', $tagNameList);

        if (!$tag) {
            return [
                'success' => false,
                'slug' => $slug,
                'message' => '請選取內容物',
            ];
        }

        // 處理金額
        $newPrice = $request->input('new_price');
        $oldPrice = $request->input('old_price');
        $discount = $request->input('discount');
        if ($oldPrice) {
            $discount = round($newPrice / $oldPrice, 2);
        } elseif ($discount) {
            $oldPrice = round($newPrice / $discount * 100, 0);
            $discount = $discount / 100;
        } else {
            return [
                'success' => false,
                'slug' => $slug,
                'message' => '請填寫原價/折數',
            ];
        }

        if ($type === "store") {
            if ($user->hasTeamRole($user->currentTeam, 'admin')) {
                // 處理slug-變更重複Coupon
                $oldCoupon = Coupon::where('slug', $slug)->first();
                if ($oldCoupon) {
                    $oldCoupon->slug = $slug . "-" . $oldCoupon->id;
                    $oldCoupon->status = CouponStatusType::EXPIRED;
                    $oldCoupon->save();
                    $message = "已更換重複Coupon的Slug..";
                }
                $status = CouponStatusType::USABLE;

                // 處理 $sub_title 清潔資料
                $content = strip_tags($content);
            } else {
                // 非admin slug 新增後綴 userId 等待審核後才轉正確
                $slug = sprintf("%s-%s-check", $slug, $userId);
                $status = CouponStatusType::PENDING;
                $message = "等待審核中..";
            }
        }

        // 日期處理 = 勾選不填寫
        $startAt = $request->input('start_at');
        $endAt   = $request->input('end_at');
        $startAt = ($startAt === '2000-01-01') ? null : $startAt;
        $endAt   = ($endAt === '2000-01-01') ? null : $endAt;

        $data = [
            'user_id'   => $userId,
            //'slug' => $slug,
            'title'     => $request->input('title'),
            'sub_title' => $request->input('sub_title'),
            'image'     => $request->input('image'),
            'content'   => $content,
            'tag'       => $tag,
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'discount'  => $discount,
            'start_at'  => $startAt,
            'end_at'    => $endAt,
            'status'    => $status,
        ];

        $newCoupon = Coupon::updateOrCreate(['slug' => $slug], $data);
        if ($user->hasTeamRole($user->currentTeam, 'admin')) {
            // admin，新增Coupon時，comment新增留言:系統通知
            $postData = [
                'user_id'    => 2,
                'tag'        => 'reply',
                'content'    => '系統通知: 由 '.$user->name.' 協助新增了這張優惠券！',
                'ip'         => '0.0.0.0',
            ];
            $newCoupon->comments()->create($postData);
        }
        pForgetCache(['c:'. $slug, 'c:home']);

        return [
            'success' => true,
            'slug'    => $slug,
            'message' => $message,
        ];
    }

    /**
     * 處理 imgur上傳圖片
     *
     * @param  Request  $request
     */
    public function imgur(Request $request)
    {
        $response = null;
        if ($request->hasFile('img_file')) {
            try {
                $response = (new ImgurHandler)->upload($request);
            } catch (\Exception $ex) {
                Log::channel('imgur_request')->error(json_encode([
                    'exception' => $ex->getMessage(),
                ]));
            }
        }
        return Arr::get($response, 'data.id', '');
    }
}
