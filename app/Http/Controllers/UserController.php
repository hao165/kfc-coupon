<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * 修改會員資料-頁面
     */
    public function edit()
    {
        $user = Auth::user();
        $canEdit = $user->hasTeamPermission($user->currentTeam, 'user:edit');
        return view('member.edit', compact('user', 'canEdit'));
    }
    /**
     * 修改會員資料-功能
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // 權限判斷
        if (!$user->hasTeamPermission($user->currentTeam, 'user:edit')) {
            return Redirect()->route('member.edit')->with('message', '權限不足！');
        }

        $request->validate([
            'name' => 'required|min:1|max:10',
        ], [
            'name.required' => '暱稱 請填寫',
            'name.min' => '暱稱 長度介於1-10字',
            'name.max' => '暱稱 長度介於1-10字',
        ]);

        $member = User::find($user->id);
        if (!$member) {
            return Redirect()->route('member.edit')->with('message', '帳號異常！');
        }

        $member->name = $request->input('name');
        $member->save();
        return Redirect()->route('member.edit')->with('message', '更新成功！');
    }
}
