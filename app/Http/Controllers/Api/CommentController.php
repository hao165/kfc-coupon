<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Coupon Card Show Comments
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $slug = $request->input('slug');

        if (!$slug) {
            return response()->json([
                "success" => false,
                "message" => "填寫資料有誤"
            ]);
        }

        // redis 人氣統計
        $ip = $request->ip();
        if ($ip != config('app.ip')) {
            pCacheViewCou($slug, $ip, false);
        }

        $comments = Coupon::where('slug', $slug)->first()->comments()
                        ->orderby('created_at', 'DESC')
                        ->take(5)
                        ->join('users', 'comments.user_id', '=', 'users.id')
                        ->select('users.name', 'comments.*')
                        ->get();

        return response()->json($comments);
    }

    /**
     * Coupon Card Add Comment
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $team = $user->currentTeam;

        $slug    = $request->input('slug');
        $tag     = $request->input('tag');
        $content = $request->input('content');
        if ((!$slug) || (!$tag) || (!$content)) {
            return response()->json([
                "success" => false,
                "message" => "填寫資料有誤"
            ]);
        }

        // 權限判斷
        if (!$user->hasTeamPermission($team, 'comment:create')) {
            return response()->json([
                "success" => false,
                "message" => "權限不足"
            ]);
        }

        $coupon = Coupon::where('slug',$slug)->first();
        if(!$coupon) {
            return response()->json([
                "success" => false,
                "message" => "找不到該優惠券"
            ]);
        }

        $postData = [
            "user_id"   => $user->id,
            "coupon_id" => $coupon->id,
            "tag"       => $tag,
            "content"   => strip_tags($content),
            "ip"        => request()->ip(),
        ];

        Comment::create($postData);
        $coupon->increment('comment_cou');

        // 全站動態通知
        if($user->id!='1'){
            pSiteLineNotify("Comment\n\n【" . $slug . "】" . $tag . "\n\n" . strip_tags($content));
        }

        // 清除快取
        pForgetCache('cmt:' . $slug);

        return response()->json([
            "success" => true,
            "message" => ""
        ]);
    }
}
