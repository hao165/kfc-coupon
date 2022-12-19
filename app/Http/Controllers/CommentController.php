<?php

namespace App\Http\Controllers;

use App\Models\Comment;

class CommentController extends Controller
{
    /**
     * 討論區 - 列表
     */
    public function index($id)
    {
        if (!is_numeric($id)) {
            return redirect()->route('chat.index', '1');
        }

        // Note: comment name 使用魔術方法，故使用join
        $list = Comment::orderby('created_at', 'desc')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('coupons', 'comments.coupon_id', '=', 'coupons.id')
            ->select('users.name', 'coupons.title', 'coupons.slug', 'comments.*');

        $paginate = pBuildPaginate($id, $list);

        return view('chat.index', compact('id', 'paginate'));
    }
}
