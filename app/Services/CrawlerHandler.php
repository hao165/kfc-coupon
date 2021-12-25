<?php

namespace App\Services;

use App\Models\Crawler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use QL\QueryList;

class CrawlerHandler
{
    /**
     * 新增 追蹤文章到資料庫 - Handler
     */
    function addPostHandler($url)
    {
        if (!$url) {
            return [
                'success' => false,
                'message' => '請輸入網址',
            ];
        }

        // 取得文章資訊
        $post_header = $this->getPostHeader($url);
        $crawler = Crawler::updateOrCreate(
            ['slug' => $post_header['slug']],
            [
                'cls' => $post_header['cls'],
                'title' => $post_header['title'],
                'status' => 1,
            ]
        );

        $addCou = $this->addReplyHandler($url, $crawler);

        return [
            'success' => true,
            'message' => '[' . $post_header['cls'] . '] <br>' . $post_header['title'] . ' <br>回覆數：' . $addCou,
        ];
    }

    /**
     * 執行 抓取全部文章的新留言
     */
    function checkAllPost()
    {
        $crawlers = Crawler::where('status', 1)->get();
        $result = '';
        foreach ($crawlers as $crawler) {
            $url = $crawler->url;
            $addCou = $this->addReplyHandler($url, $crawler);
            $result .= $crawler->cls . ' : ' . $crawler->slug . "，新增" . $addCou . "筆" . PHP_EOL;
        }
        return $result;
    }

    /**
     * 將清潔後的留言新增到資料庫 - Handler
     */
    function addReplyHandler($url, $crawler)
    {
        //取得最後一則回覆時間
        $last_at = $crawler->last_at;
        if (!$last_at) {
            $last_at = '2021-01-01 00:00:00';
        }
        $last_at = strtotime($last_at);

        //取得回覆
        $replys = $this->getReplys($url);
        if(!$replys){
            return 0;
        }
        $addCou = 0;
        $crawlerItems = $crawler->items();
        foreach ($replys as $val) {
            if ($last_at < strtotime($val['created_at'])) {
                $crawlerItems->updateOrCreate(['ptt_id' => $val['ptt_id'], 'created_at' => $val['created_at']], ['content' => $val['content']]);
                $new_last_at = $val['created_at'];
                $addCou++;
                pSiteLineNotify("Crawler\n\n". $val['content']);
            }
        }

        if ($addCou > 0) {
            $crawler->last_at = $new_last_at;
            $crawler->save();
        }
        return $addCou;
    }

    /**
     * 爬蟲QueryList，進行清潔資料
     *
     * @param  String $url
     * @return Array
     */
    function getReplys($url)
    {
        $ql = QueryList::getInstance();
        $list = $ql->get($url)
            ->rules([
                'ptt_id'     => array('.push-userid', 'texts'),
                'content'    => array('.push-content', 'texts'),
                'created_at' => array('.push-ipdatetime', 'texts'),
            ])
            ->query()->getData();
        if(!$list['ptt_id']){
            return;
        }

        $arr = ['ptt_id', 'content', 'created_at'];

        foreach ($arr as $tag) {
            foreach ($list[$tag] as $key => $val) {
                if ($tag == 'content') {
                    $val = Str::replaceFirst(': ', '', $val);
                } else if ($tag == 'created_at') {
                    $val = substr($val, -11);
                    $val = '2021/' . $val;
                    $val = date("Y-m-d H:i:s", strtotime($val));
                }
                $data[$key][$tag] = $val;
            }
        }

        $arr_count = count($data);
        for ($key = 0; $key < $arr_count; $key++) {
            if (isset($data[$key + 1])) {
                if ($data[$key]['ptt_id'] == $data[$key + 1]['ptt_id']) {
                    $data[$key]['content'] = $data[$key]['content'] . $data[$key + 1]['content'];
                    $data[$key]['created_at'] = $data[$key + 1]['created_at'];
                    unset($data[$key + 1]);
                    $key = $key - 1;
                    $data = array_values($data);
                }
            }
        }
        return $data;
    }

    /**
     * (棄用) 執行 抓取文章新留言-單篇
     */
    function checkPost($url)
    {
        if (!$url) {
            return '請輸入網址';
        }

        $post_header = $this->getPostHeader($url);

        $crawler = Crawler::where('slug', $post_header['slug'])->first();
        if (!$crawler) {
            return '請先新增網址';
        }

        $result = $this->addReplyHandler($url, $crawler);
        return '已新增' . $result;
    }

    /**
     * 爬蟲QueryList，取得文章 看板、Slug、標題
     *
     * @param  String $url
     * @return Array
     */
    function getPostHeader($url)
    {
        $ql = QueryList::getInstance();
        $title = $ql->get($url)
            ->find('.article-metaline:eq(1) > .article-meta-value')->text();
        $url = Str::replaceFirst('https://www.ptt.cc/bbs/', '', $url);
        $url = Str::replaceLast('.html', '', $url);
        $str = explode("/", $url);
        $array = [
            'cls'   => $str[0],
            'slug'  => $str[1],
            'title' => $title,
        ];
        return $array;
    }
}
?>
