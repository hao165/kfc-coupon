<?php

namespace App\Http\Controllers;

use App\Models\Collect;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CollectController extends Controller
{

    /**
     * 個人收藏
     */
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        $coupons = Collect::where('collects.user_id', $userId);
        $list = $coupons->pluck('coupon_id')->all();

        $collects = $coupons
            ->join('coupons', 'collects.coupon_id', '=', 'coupons.id')
            ->pluck('coupons.slug')->all();

        if(!count($list)) {
            $list = [];
            $message = "目前尚未有個人收藏的Coupon！";
        }else{
            $list = Coupon::whereIn('id',$list)->get();
            $message = "";
        }

        return view('collect.index', compact('list', 'collects', 'message'));
    }

    /**
     * 個人收藏-編輯
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $slug = $request->input('slug');
        if (!$slug) {
            return response()->json([
                "success" => false,
                "message" => "請選擇優惠券",
            ]);
        }

        // 權限判斷
        if (!$user->hasTeamPermission($team, 'collect')) {
            return response()->json([
                "success" => false,
                "message" => "權限不足",
            ]);
        }

        // 優惠券是否存在
        $coupon = Coupon::where('slug', $slug)->first();
        if (!$coupon) {
            return response()->json([
                "success" => false,
                "message" => "優惠券異常",
            ]);
        }

        $couponId = $coupon->id;
        $userId = $user->id;
        $collect = Collect::where('user_id', $userId)->where('coupon_id', $couponId)->first();

        // 新增
        if (!$collect) {
            Collect::create(
                ['user_id' => $userId, 'coupon_id' => $couponId]
            );
            $coupon->increment('collect_cou');
            return response()->json([
                "success" => true,
                "action" => 'add',
                "message" => "該優惠券已從收藏中 新增 ！",
            ]);
        }

        // 刪除
        Collect::destroy($collect->id);
        $coupon->decrement('collect_cou');
        return response()->json([
            "success" => true,
            "action" => 'del',
            "message" => "該優惠券已從收藏中 刪除 ！",
        ]);
    }

    /**
     * 收藏榜
     */
    public function rank()
    {
        $list = Collect::orderBy('count', 'desc')
            ->select(DB::raw('coupon_id,count(*) as count'))
            ->groupBy('coupon_id')
            ->take(15)
            ->get();

        $collects = [];
        if ($user = Auth::user()) {
            $collects = $user->have_collects;
        }

        if (!count($list)) {
            $list = [];
            $message = "目前收藏榜尚無Coupon！";
        } else {
            $rank = $list->pluck('coupon_id')->all();
            $list = Coupon::whereIn('id', $rank)
                ->orderByRaw(DB::raw("field(id," . implode(',', $rank) . ")"))
                ->get();
            $message = "";
        }

        return view('collect.rank', compact('list', 'collects', 'message'));
    }

}
