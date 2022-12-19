<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Crawler;
use App\Models\CrawlerItem;
use App\Services\CrawlerHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrawlerController extends Controller
{
    /**
     * 後台管理 > 爬蟲文章 列表
     */
    public function postList()
    {
        // 權限判斷
        $user = Auth::user();
        if (!$user->hasTeamPermission($user->currentTeam, 'crawler')) {
            return redirect()->route('coupons.index');
        }

        $list = Crawler::where('status', '!=', 3)->orderBy('id', 'desc')->get();

        return view('admin.crawler.post-list', compact('list'));
    }

    /**
     * 後台管理 > 爬蟲回覆 列表
     */
    public function itemList($id)
    {
        // 權限判斷
        $user = Auth::user();
        if (!$user->hasTeamPermission($user->currentTeam, 'crawler')) {
            return redirect()->route('coupons.index');
        }

        $list = CrawlerItem::where('status', 0)->orderBy('id', 'desc');
        $paginate = pBuildPaginate($id, $list);
        return view('admin.crawler.item-list', compact('paginate'));
    }

    /**
     * 後台管理 > 爬蟲回覆 轉發布 by 機器人小肯
     */
    public function itemPush(Request $request)
    {
        // 權限判斷
        $user = Auth::user();
        if (!$user->hasTeamPermission($user->currentTeam, 'crawler')) {
            return [
                'success' => false,
                'message' => '權限不足',
            ];
        }
        $slug    = $request->input('slug');
        $time    = $request->input('time');
        $tag     = $request->input('tag');
        $content = $request->input('content');
        $itemId  = $request->input('itemId');

        // 新增comment
        $postData = [
            'user_id'    => 2,
            'tag'        => $tag,
            'content'    => $content,
            'ip'         => '0.0.0.0',
            'created_at' => $time,
        ];
        $coupon = Coupon::where('slug', $slug)->first();
        if (!$coupon) {
            return [
                'success' => false,
                'message' => '找不到對應的 Slug',
            ];
        }

        $result = $coupon->comments()->create($postData);

        // 變更item狀態 為 發布
        if ($commentId = $result->id) {
            $coupon->increment('comment_cou');
            $item = CrawlerItem::find($itemId);
            $item->status = 1;
            $item->save();

            return [
                'success' => true,
                'message' => '機器人小肯已新增回應[' . $commentId . ']',
            ];
        }

        return [
            'success' => false,
            'message' => '機器人小肯 新增回應 失敗！[' . $result . ']',
        ];
    }

    /**
     * 後台管理 > 爬蟲回覆 手動執行抓取
     */
    public function itemCheck()
    {
        // 權限判斷
        $user = Auth::user();
        if (!$user->hasTeamPermission($user->currentTeam, 'crawler')) {
            return [
                'success' => false,
                'message' => '權限不足',
            ];
        }

        $result = (new CrawlerHandler())->checkAllPost();
        $result = str_replace(PHP_EOL, '<br>', $result);

        return [
            'success' => true,
            'message' => '[手動執行完成]<br>' . $result,
        ];
    }

    /**
     * 新增 追蹤文章
     */
    public function store(Request $request)
    {
        // 權限判斷
        $user = Auth::user();
        if (!$user->hasTeamPermission($user->currentTeam, 'crawler')) {
            return [
                'success' => false,
                'message' => '權限不足',
            ];
        }

        $url = $request->input('url');
        $result = (new CrawlerHandler())->addPostHandler($url);

        return $result;
    }

    /**
     * 修改 文章狀態
     */
    public function switchStatus(Request $request)
    {
        // 權限判斷
        $user = Auth::user();
        if (!$user->hasTeamPermission($user->currentTeam, 'crawler')) {
            return [
                'success' => false,
                'message' => '權限不足',
            ];
        }

        $id     = $request->input('id');
        $status = $request->input('status');
        $item   = Crawler::find($id);
        if (is_null($item) || is_null($status)) {
            return [
                'success' => false,
                'message' => '找不到該編號[' . $id . '] 或 狀態 [' . $status . ']',
            ];
        }

        $item->status = $status;
        $item->save();

        switch ($status) {
            case 1:
                $statusTxt = '啟用';
                break;
            case 2:
                $statusTxt = '停用';
                break;
            case 3:
                $statusTxt = '刪除';
                break;
        }

        return [
            'success'=> true,
            'message' => '編號['. $id.']，已變更為 '.$statusTxt,
        ];
    }
}
