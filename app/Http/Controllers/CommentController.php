<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * 討論區 - 列表
     */
    public function index($id)
    {
        $list = Comment::orderby('created_at', 'DESC')
            ->join('users', 'comments.user_id', '=', 'users.id')
            ->join('coupons', 'comments.coupon_id', '=', 'coupons.id')
            ->select('users.name', 'coupons.title', 'coupons.slug', 'comments.*');
        $paginate = pBuildPaginate($id, $list);

        return view('chat.index', compact('id','paginate'));
    }
}
