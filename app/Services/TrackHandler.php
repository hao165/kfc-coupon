<?php

namespace App\Services;

use App\Models\Track;
use App\Models\TrackItem;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use QL\QueryList;
use App\Services\Api\LineNotifyHandler;

class TrackHandler
{
    /**
     * 執行爬蟲
     *
     * @return Int 符合筆數
     */
    public function trackHandle()
    {
        $track = Track::orderBy('updated_at')->first();
        if (!$track) {
            return;
        }

        $trackId  = $track->id;
        $userId   = $track->user_id;
        $url      = $track->url;
        $page     = $track->page;
        $keywords = $track->keyword;
        $push     = $track->push;

        $ql = QueryList::getInstance();
        for ($i = 0; $i < $page; $i++) {
            $item = $ql->get($url)->find('.r-ent > .title > a');
            $ptt['url'][$i]   = json_decode($item->attrs('href'));
            $ptt['push'][$i]  = json_decode($item->parent()->parent()->find('.nrec')->texts());
            $ptt['title'][$i] = json_decode($item->texts());
            $ptt['who'][$i]   = json_decode($item->parent()->parent()->find('.meta > .author')->texts());
            //下一頁
            $url = "https://www.ptt.cc" . $ql->get($url)->find('.btn-group-paging > .wide:eq(1)')->attr('href');
        }

        $ptt['url']   = Arr::collapse($ptt['url']);
        $ptt['push']  = Arr::collapse($ptt['push']);
        $ptt['title'] = Arr::collapse($ptt['title']);
        $ptt['who']   = Arr::collapse($ptt['who']);

        foreach ($ptt as $tag => $item) {
            foreach ($ptt[$tag] as $key => $val) {
                $val = ($val === '爆') ? '100' : $val;
                $data[$key][$tag] = $val;
            }
        }

        $cou = 0;
        foreach ($data as $key => $item) {
            $data[$key]['type'] = null;
            foreach ($item as $tag => $val) {
                if ($tag === 'who') {
                    break;
                }
                if (($tag === 'push') && $push != '0') {
                    if (intval($val) >= $push) {
                        $data[$key]['type'] = 'push';
                    }
                }
                if ($tag === 'title') {
                    $data[$key]['title'] = sprintf('%s 作者:%s', $data[$key]['title'], $data[$key]['who']);
                    foreach ($keywords as $keyword) {
                        if (strpos(strtolower($data[$key]['title']), $keyword) !== false) {
                            $data[$key]['type'] = 'keyword';
                        }
                    }
                    // 排除公告
                    if (strpos($val, '公告') !== false) {
                        $data[$key]['type'] = null;
                    }
                }
            }
            if ($data[$key]['type']) {
                $cou++;
                TrackItem::firstOrCreate(['user_id' => $userId, 'url' => $data[$key]['url']], [
                    'track_id' => $trackId,
                    'type' => $data[$key]['type'],
                    'title' => $data[$key]['title'],
                    'url' => $data[$key]['url'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        $track->updated_at = date('Y-m-d H:i:s');
        $track->save();

        return $cou;
    }

    /**
     * 執行通知
     *
     * @return Int 通知筆數
     */
    public function notifyHandle()
    {
        $list = TrackItem::where('status', '=', '0')->where('id', '=', '1')->get();
        $cou = 0;
        foreach ($list as $item) {
            $token = $item->user->line_notify;
            $text = "{$item->type_name}\n【{$item->url}】\n{$item->title}\n{$item->url}";

            $result = (new LineNotifyHandler())->send($token, $text);
            if ($result) {
                $cou++;
            }
        }
        return $cou;
    }
}
