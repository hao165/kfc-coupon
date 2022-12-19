<?php

namespace App\Services;

use App\Models\Crawler;
use Illuminate\Support\Str;
use QL\QueryList;

class CrawlerHandler
{
    /**
     * 新增 追蹤文章到資料庫 - Handler
     */
    public function addPostHandler($url)
    {
        if (!$url) {
            return [
                'success' => false,
                'message' => '請輸入網址',
            ];
        }

        // 取得文章資訊
        $postHeader = $this->getPostHeader($url);
        $crawler = Crawler::updateOrCreate(
            ['slug' => $postHeader['slug']],
            [
                'cls' => $postHeader['cls'],
                'title' => $postHeader['title'],
                'status' => 1,
            ]
        );

        $addCou = $this->addReplyHandler($url, $crawler);

        return [
            'success' => true,
            'message' => sprintf('[%s] <br>%s<br>回覆數：%s', $postHeader['cls'], $postHeader['title'], $addCou),
        ];
    }

    /**
     * 執行 抓取全部文章的新留言
     */
    public function checkAllPost()
    {
        $result = '';

        $crawlers = Crawler::where('status', 1)->get();
        foreach ($crawlers as $crawler) {
            $addCou = $this->addReplyHandler($crawler->url, $crawler);
            $result .= sprintf('%s : %s，新增%s筆', $crawler->cls, $crawler->slug, $addCou) . PHP_EOL;
        }
        return $result;
    }

    /**
     * 將清潔後的留言新增到資料庫 - Handler
     */
    public function addReplyHandler($url, $crawler)
    {
        // 取得回覆
        $replys = $this->getReplys($url);
        if (!$replys) {
            return 0;
        }

        // 只抓取最後150則回覆
        $startReplyNo = (count($replys) > 150) ? (count($replys) - 150) : 0;

        // 取得最後一則回覆時間
        $lastAt = strtotime($crawler->last_at ?? '2021-01-01 00:00:00');

        $addCou = 0;
        $crawlerItems = $crawler->items();
        foreach ($replys as $key => $val) {
            if ($key < $startReplyNo) {
                continue;
            }
            if ($lastAt < strtotime($val['created_at'])) {
                $crawlerItems->updateOrCreate(['ptt_id' => $val['ptt_id'], 'created_at' => $val['created_at']], ['content' => $val['content']]);
                $newLastAt = $val['created_at'];
                $addCou++;
                pSiteLineNotify("Crawler\n\n". $val['content']);
            }
        }

        if ($addCou > 0) {
            $crawler->last_at = $newLastAt;
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
    public function getReplys($url)
    {
        $ql = QueryList::getInstance();
        $list = $ql->get($url)
            ->rules([
                'ptt_id'     => array('.push-userid', 'texts'),
                'content'    => array('.push-content', 'texts'),
                'created_at' => array('.push-ipdatetime', 'texts'),
            ])
            ->query()->getData();
        if (!$list['ptt_id']) {
            return;
        }

        $arr = ['ptt_id', 'content', 'created_at'];
        foreach ($arr as $tag) {
            foreach ($list[$tag] as $key => $val) {
                if ($tag === 'content') {
                    $val = Str::replaceFirst(': ', '', $val);
                } elseif ($tag === 'created_at') {
                    // val = 12/13 12:28
                    $val = substr($val, -11);
                    $mon = substr($val, 0, 2);
                    $year = ($mon > date('m')) ? date("Y", strtotime("-1 year")) : date("Y");
                    $val = sprintf('%s/%s', $year, $val);
                    $val = date("Y-m-d H:i:s", strtotime($val));
                }
                $data[$key][$tag] = $val;
            }
        }

        $arrCount = count($data);
        for ($key = 0; $key < $arrCount; $key++) {
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
    public function checkPost($url)
    {
        if (!$url) {
            return '請輸入網址';
        }

        $postHeader = $this->getPostHeader($url);

        $crawler = Crawler::where('slug', $postHeader['slug'])->first();
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
    public function getPostHeader($url)
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
