<?php

namespace App\Http\Controllers;

use App\Models\Collect;
use App\Models\Coupon;
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
        $user     = Auth::user();
        $collects = $user->collect_list;
        $list     = $user->collects->pluck('coupon')->sortByDesc('view_cou')->all(); // model coupon
        $message  = count($list) ? "" : "目前尚未有個人收藏的Coupon！";

        return view('collect.index', compact('list', 'collects', 'message'));
    }

    /**
     * 個人收藏-編輯
     *
     * @param  Request  $request
     */
    public function update(Request $request)
    {
        $slug = $request->input('slug');
        if (is_null($slug)) {
            return response()->json([
                "success" => false,
                "message" => "請選擇優惠券",
            ]);
        }

        // 權限判斷
        $user = Auth::user();
        if (!$user->hasTeamPermission($user->currentTeam, 'collect')) {
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
        $collect  = $user->collects->where('coupon_id', $couponId)->first();

        if (!$collect) {
            // 新增
            $user->collects()->create(
                ['coupon_id' => $couponId]
            );
            $coupon->increment('collect_cou');
            return response()->json([
                "success" => true,
                "action" => 'add',
                "message" => "該優惠券已從收藏中 新增 ！",
            ]);
        } else {
            // 刪除
            $collect->delete();
            $coupon->decrement('collect_cou');
            return response()->json([
                "success" => true,
                "action" => 'del',
                "message" => "該優惠券已從收藏中 刪除 ！",
            ]);
        }
    }

    /**
     * 收藏榜
     */
    public function rank()
    {
        $user = Auth::user();
        $collects = $user ? $user->collect_list : [];

        $query = Collect::with('coupon')
            ->orderBy('count', 'desc')
            ->select(DB::raw('coupon_id, count(id) as count'))
            ->groupBy('coupon_id')
            ->take(20)
            ->get();

        $list = $query->pluck('coupon')->all(); // model coupon

        $message = count($list) ? "" : "目前收藏榜尚無Coupon！";

        return view('collect.rank', compact('list', 'collects', 'message'));
    }
}
