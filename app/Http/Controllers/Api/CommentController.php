<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Coupon Card Show Comments
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $slug = $request->input('slug');
        if (is_null($slug)) {
            return response()->json([
                "success" => false,
                "message" => "填寫資料有誤"
            ]);
        }

        $comments = Coupon::where('slug', $slug)->first()->comments()
            ->orderby('created_at', 'desc')
            ->take(5)
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->select('users.name', 'comments.*')
            ->get();

        pCacheViewCou($slug);

        return response()->json($comments);
    }

    /**
     * Coupon Card Add Comment
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $slug    = $request->input('slug');
        $tag     = $request->input('tag');
        $content = $request->input('content');
        if (is_null($slug) || is_null($tag) || is_null($content)) {
            return response()->json([
                "success" => false,
                "message" => "填寫資料有誤"
            ]);
        }

        // 權限判斷
        $user = Auth::user();
        if (!$user->hasTeamPermission($user->currentTeam, 'comment:create')) {
            return response()->json([
                "success" => false,
                "message" => "權限不足"
            ]);
        }

        $coupon = Coupon::where('slug', $slug)->first();
        if (!$coupon) {
            return response()->json([
                "success" => false,
                "message" => "找不到該優惠券"
            ]);
        }

        $coupon->comments()->create([
            "user_id"   => $user->id,
            "tag"       => $tag,
            "content"   => strip_tags($content),
            "ip"        => request()->ip(),
        ]);
        $coupon->increment('comment_cou');

        // 全站動態通知
        if ($user->id != '1') {
            pSiteLineNotify("Comment-" . $tag . "\n\n(" . $slug . ") " . strip_tags($content));
        }

        // 清除快取
        pForgetCache(sprintf('cmt:%s', $slug));

        return response()->json([
            "success" => true,
            "message" => ""
        ]);
    }
}
