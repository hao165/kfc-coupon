<?php

namespace App\Http\Controllers;

use App\Constant\CouponStatusType;
use App\Models\Coupon;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Constant\FoodType;
use App\Models\Collect;
use App\Models\User;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;


class CouponController extends Controller
{
    protected $food_array_common, $food_array_less, $food_array_drink, $food_array,
              $food_array_more;


    public function __construct()
    {
        $this->food_array_common = FoodType::FOOD_COMMON;
        $this->food_array_less = FoodType::FOOD_LESS;
        $this->food_array_drink = FoodType::FOOD_DRINK;
        $this->food_array = array_merge($this->food_array_common, $this->food_array_less, $this->food_array_drink);

        $this->food_array_more = FoodType::FOOD_MORE;
    }

    /**
     * 頁面 - 最新優惠券
     */
    public function index()
    {
        $food_array_common = $this->food_array_common;
        $food_array_less = $this->food_array_less;
        $food_array_drink = $this->food_array_drink;
        $food_array_more = $this->food_array_more;

        $list = pCacheDb('c:home', function () {
            return Coupon::where('status', CouponStatusType::USABLE)->get();
        }, false, 3600);

        $collects=[];
        if($user = Auth::user()) {
            $collects = $user->have_collects;
        }

        $pageWord = "最新優惠券";

        return view('coupons.index', compact('list', 'pageWord', 'collects', 'food_array_common', 'food_array_less', 'food_array_drink', 'food_array_more'));
    }

    /**
     * 頁面 - 過期優惠券
     */
    public function expired()
    {
        $list = pCacheDb('c:exp', function () {
            return Coupon::where('status', CouponStatusType::EXPIRED)->orderBy('view_cou','desc')->get();
        }, false, 86400);

        $pageWord = "過期優惠券";

        $collects = [];
        if ($user = Auth::user()) {
            $collects = $user->have_collects;
        }

        return view('coupons.index', compact('list', 'collects', 'pageWord'));
    }

    /**
     * 頁面 - 優惠券內頁
     *
     * @param  string  $slug
     * @param  \Illuminate\Http\Request  $request
     */
    public function show($slug, Request $request)
    {
        if (!$slug) {
            return 'ERROR 404!';
        }

        $ip = $request->ip();
        $isHost = true;
        // 棄用-redis 人氣統計，改從前端判斷是否為真人瀏覽
        if ($ip != config('app.ip')) {
            //pCacheViewCou($slug, $ip, false);
            $isHost = false;
        }

        // Coupon 為 已審核
        $coupon = pCacheDb('c:' . $slug, function () use ($slug) {
            return Coupon::where('slug', $slug)
                ->where(function ($query) {
                    $query->where('status', CouponStatusType::USABLE)
                        ->orWhere('status', CouponStatusType::EXPIRED);
                })
                ->first();
        }, $isHost);

        $user = Auth::user();
        if (!$coupon) {
            // 待審核 Coupon
            if (!$user) {
                return view('coupons.404', compact('slug'));
            }
            $coupon = Coupon::where('slug', $slug)->where('status', CouponStatusType::PENDING)->first();
            if (!$coupon) {
                return view('coupons.404', compact('slug'));
            }
            // 確認是否為role-admin或是本人
            $isAdmin = $user->hasTeamRole($user->currentTeam, 'admin');
            if (($coupon->user_id != $user->id) && (!$isAdmin)) {
                return view('coupons.404', compact('slug'));
            }
            return view('coupons.pending', compact('coupon', 'isAdmin'));
        }

        $collects = [];
        if ($user) {
            $collects = $user->have_collects;
        }

        // Comments 快取 30分鐘
        $comments = pCacheDb('cmt:' . $slug, function () use ($coupon) {
            return $coupon->comments()
                ->orderby('created_at', 'DESC')
                ->take(10)
                ->join('users', 'comments.user_id', '=', 'users.id')
                ->select('users.name', 'comments.*')
                ->get();
        }, $isHost, 1800);

        if (!$comments) {
            $comments = [];
        }

        return view('coupons.show', compact('coupon', 'collects', 'comments'));
    }

    /**
     * 頁面 - 新增優惠券
     */
    public function create()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        // 權限判斷
        if(!$user->hasTeamPermission($team, 'create')){
            return redirect()->route('coupons.index');
        }

        $food_array = $this->food_array;
        return view('coupons.create',compact('food_array'));
    }

    /**
     * 功能 - 新增優惠券
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;
        $request->flash();

        // 權限判斷
        if (!$user->hasTeamPermission($team, 'create')) {
            return redirect()->route('coupons.index');
        }

        $request->validate([
            'title' => 'digits_between:5,5',
            'new_price' => 'required|integer',
            'image' => 'required',
            'start_at' => 'required|date',
            'end_at' => 'required|date',
        ], [
            'title.digits_between' => '優惠券代號 請填寫五碼數字',
            'new_price.required' => '售價 請填寫',
            'new_price.integer' => '售價 請填寫數字',
            'image.required' => '圖片 請透過本站上傳',
            'start_at.required' => '開始時間 請確認',
            'start_at.date' => '開始時間 請確認',
            'end_at.required' => '結束時間 請確認',
            'end_at.date' => '結束時間 請確認',
        ]);

        $result = (object) $this->handle($request, $user, 'store');

        $message = '新增成功！ '.$result->message.'  <a class="text-decoration-none" href="' . route('coupons.show', $result->slug) . '" target="_blank">查看</a>';

        // 全站動態通知
        if($result->message){
            pSiteLineNotify("Coupon\n\n" . $result->message . "\n\n" . route('coupons.show', $result->slug));
        }

        if (!$result->success) {
            $message = $result->message;
        }

        return Redirect()->route('coupons.create')->with('message', $message);
    }

    /**
     * 功能 - 計數器 : 前端判斷為真人瀏覽
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function viewCount(Request $request)
    {
        $slug = $request->input('slug', '');
        $ip = $request->input('ip', '');
        if(!$slug || !$ip){
            return;
        }
        if($ip != config('app.ip')){
            pCacheViewCou($slug, $ip, false);
        }
    }

    /**
     * 頁面 - 修改優惠券 (目前僅role-admin)
     *
     * @param  string  $slug
     */
    public function edit($slug)
    {
        if (!$slug) {
            return 'ERROR 404!';
        }

        $user = Auth::user();
        $team = $user->currentTeam;

        // 權限判斷
        if (!$user->hasTeamPermission($team, 'update')) {
            return redirect()->route('coupons.index');
        }

        $coupon = Coupon::where('slug', $slug)->first();
        if (!$coupon) {
            return view('coupons.404', compact('slug'));
        }

        $food_array = $this->food_array;

        return view('coupons.edit', compact('coupon', 'food_array'));
    }

    /**
     * 功能 - 修改優惠券 (目前僅role-admin)
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $slug = $request->input('slug', $request->input('title'));

        // 權限判斷
        if (!$user->hasTeamPermission($team, 'update')) {
            return redirect()->route('coupons.show', $slug);
        }

        $result = (object) $this->handle($request, $user, 'update');

        $message = '更新已送出！  <a class="text-decoration-none" href="' . route('coupons.show', $result->slug) . '" target="_blank">查看</a>';

        if(!$result->success){
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
        $user = Auth::user();
        $team = $user->currentTeam;

        // 權限判斷
        if (!$user->hasTeamPermission($team, 'delete')) {
            return redirect()->route('coupons.index');
        }

        $coupon = Coupon::where('slug', $slug)->first();
        if(!$coupon) {
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
        $user = Auth::user();
        $team = $user->currentTeam;

        // 權限判斷
        if (!$user->hasTeamPermission($team, 'verify')) {
            return redirect()->route('coupons.index');
        }

        $coupon = Coupon::where('id', $id)->first();
        if (!$coupon) {
            return back();
        }

        if ($type == 'pass') {
            $coupon->slug = $coupon->title;
            $coupon->status = CouponStatusType::USABLE;
            $coupon->save();
        } else if ($type == 'fail') {
            $coupon->slug = $coupon->slug . "-fail";
            $coupon->status = CouponStatusType::FAIL;
            $coupon->save();
            $coupon->delete();
        } else{
            return "type異常";
        }

        return redirect()->route('coupons.show',$coupon->slug);
    }

    /**
     * 頁面 - 待審核列表 (目前僅role-admin)
     */
    public function pendingList()
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        // 權限判斷
        if (!$user->hasTeamPermission($team, 'verify')) {
            return redirect()->route('coupons.index');
        }

        $list = Coupon::where('status', CouponStatusType::PENDING)->get();

        return view('admin.pending-list', compact('list'));
    }

    /**
     * 處理 新增.修改
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \app\Models\User  $User
     * @param  string  $type
     */
    public function handle(Request $request, User $user, $type)
    {
        $slug = $request->input('slug', $request->input('title'));
        $status = $request->input('status', 0);
        $userId = $user->id;
        $message = "";

        // 處理tag與content
        $tag = [];
        $content = [];

        $tags = $request->input('tag');
        $food_array = $this->food_array;
        $food_array_more = $this->food_array_more;
        foreach ($tags as $key => $value) {
            if ($value > 0) {
                $tag[] = $key;
                $the_content = $food_array[$key][0] . 'x' . $value;
                $content[] = $the_content;
                foreach ($food_array_more as $key2 => $value2) {
                    if ($the_content == $value2[0]) {
                        $tag[] = $key2;
                    }
                }
            }
        }
        $tag = implode(' ', $tag);
        $content = implode(' + ', $content);

        if (!$tag) {
            return [
                'success' => false,
                'slug' => $slug,
                'message' => '請選取內容物',
            ];
        }

        // 處理金額
        $new_price = $request->input('new_price');
        $old_price = $request->input('old_price');
        $discount  = $request->input('discount');
        if ((!$old_price) && (!$discount)) {
            return [
                'success' => false,
                'slug' => $slug,
                'message' => '請填寫原價/折數',
            ];
        }
        if ($old_price) {
            $discount = round($new_price / $old_price, 2);
        } else if ($discount) {
            $old_price = round($new_price / $discount * 100, 0);
            $discount = $discount / 100;
        }

        $sub_title = $request->input('sub_title');
        if($type== "store"){
            if($user->hasTeamRole($user->currentTeam, 'admin')) {
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


            }else{
                // 非admin slug 新增後綴 userId 等待審核後才轉正確
                $slug = $slug . "-" . $userId . "-check";
                $status = CouponStatusType::PENDING;
                $message = "等待審核中..";
            }
        }

        // 日期處理 = 勾選不填寫
        $start_at = $request->input('start_at');
        if($start_at == '2000-01-01'){
            $start_at = NULL;
        }
        $end_at = $request->input('end_at');
        if ($end_at == '2000-01-01') {
            $end_at = NULL;
        }

        $data = [
            'user_id' => $userId,
            //'slug' => $slug,
            'title' => $request->input('title'),
            'sub_title' => $sub_title,
            'image' => $request->input('image'),
            'content' => $content,
            'tag' => $tag,
            'old_price' => $old_price,
            'new_price' => $new_price,
            'discount' => $discount,
            'start_at' => $start_at,
            'end_at' => $end_at,
            'status' => $status,
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
        pForgetCache(['c_'. $slug, 'c_home']);

        return [
            'success' => true,
            'slug'    => $slug,
            'message' => $message,
        ];
    }

    /**
     * 處理 imgur上傳圖片
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function imgur(Request $request)
    {
        $client_id = config('services.imgur.client_id');
        $client_secret = config('services.imgur.client_secret');

        if ($request->hasFile('img_file')) {

            $requestImg = $request->file('img_file');

            // 暫存路徑
            $img_temp = rand().'.jpg';
            $img_temp_path = storage_path('app/temp/' . $img_temp);

            // 新增浮水印
            $img = Image::make($requestImg->getRealPath());
            $imgMark = Image::make('watermark.png');
            $img_width = $img->width();
            $imgMark_width = round($img_width*0.25);
            $imgMark->resize($imgMark_width, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            $img->insert($imgMark, 'bottom-left', 30, 20)->save($img_temp_path);

            $data = fread(fopen($img_temp_path, "r"), filesize($img_temp_path));
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Client-ID ' . $client_id));
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, array('image' => base64_encode($data)));
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $out = curl_exec($curl);
            curl_close($curl);

            //刪除暫存的圖片
            Storage::delete('temp/'.$img_temp);

            $pms = json_decode($out, true);
            return $pms['data']['id'];
        }
    }
}
