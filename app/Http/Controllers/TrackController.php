<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\TrackItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrackController extends Controller
{
    /**
     * 追蹤 設定Line Notify
     */
    public function init()
    {
        $user = Auth::user();
        if ($user->line_notify) {
            return redirect()->route('admin.track.list');
        }
        $URL = 'https://notify-bot.line.me/oauth/authorize?';
        $URL .= 'response_type=code';
        $URL .= '&client_id=' . config('services.line_notify.client_id');
        $URL .= '&redirect_uri=' . route('line_notify_callback');
        $URL .= '&scope=notify';
        $URL .= '&state=NO_STATE';
        return redirect($URL);
    }

    /**
     * 追蹤 看板列表
     */
    public function list()
    {
        $user = Auth::user();
        if (!$user->line_notify) {
            return redirect()->route('admin.track.init');
        }

        $list = $user->tracks ?? [];

        return view('admin.track.list', compact('list'));
    }

    /**
     * 追蹤 看板新增/更新
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        $action = $request->input('action');
        $cls    = strtolower($request->input('cls'));
        $data   = strtolower($request->input('data'));
        $data2  = strtolower($request->input('data2'));
        if (is_null($action) || is_null($cls)) {
            return [
                'success' => false,
                'message' => '缺少參數',
            ];
        }

        $item = Track::where('user_id', $userId)->where('cls', $cls)->first();
        if ('delAll' === $action) {
            if ($item) {
                $item->delete();
            }
            return [
                'success' => true,
                'message' => '',
            ];
        }

        // 取得原值
        $page    = isset($item->page) ? $item->page : 3;
        $keyword = isset($item->keyword) ? $item->keyword : [];
        $push    = isset($item->push) ? $item->push : 0;
        $status  = isset($item->status) ? $item->status : 1;

        // 新值覆蓋
        if ('edit' === $action) {
            $page = $data;
            $push = $data2;
        } elseif (is_numeric($data)) {
            if ('add' === $action) {
                $push = $data;
            } elseif ('del' === $action) {
                $push = 0;
            }
        } else {
            if ('add' === $action) {
                array_push($keyword, $data);
                $keyword = array_unique($keyword);
            } elseif ('del' === $action) {
                $temp[0] = $data;
                $keyword = array_diff($keyword, $temp);
            }
        }
        $postData = [
            'page'      => $page,
            'keyword'   => $keyword,
            'push'      => $push,
            'status'    => $status,
        ];

        Track::updateOrCreate(['user_id' => $userId, 'cls' => $cls], $postData);

        return [
            'success' => true,
            'message' => '',
        ];
    }

    /**
     * 追蹤 看板通知
     */
    public function item($id)
    {
        $user = Auth::user();
        $userId = $user->id;
        if (!$user->line_notify) {
            return redirect()->route('admin.track.init');
        }

        $list = TrackItem::where('user_id', $userId)->orderBy('id', 'desc');
        $paginate = pBuildPaginate($id, $list);
        return view('admin.track.item', compact('paginate'));
    }

    /**
     * 追蹤 Line Notify CallBack
     */
    public function callback(Request $request)
    {
        $user = $request->user();

        // 取得 user token 並存入 User
        if (!$user->line_notify) {
            $code = $_GET['code'];
            $redirect_uri = route('line_notify_callback');
            $message = array(
                'grant_type'    => 'authorization_code',
                'code'          => $code,
                'redirect_uri'  => $redirect_uri,
                'client_id'     => config('services.line_notify.client_id'),
                'client_secret' => config('services.line_notify.client_secret'),
            );

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => 'https://notify-bot.line.me/oauth/token',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $message,
            ]);
            $result = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($result, true);
            $token = $result['access_token'];

            $user->line_notify = $token;
            $user->save();

            return redirect()->route('admin.track.list');
        }
    }
}
