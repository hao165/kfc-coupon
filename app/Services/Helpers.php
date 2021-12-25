<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\LineNotifyHandler;

/*
|--------------------------------------------------------------------------
| 全域 helper
|--------------------------------------------------------------------------
|
| 有共用且須全域皆可使用，可以建立於此，方便使用
|
 */

if (!function_exists('pBuildPaginate')) {
    /**
     * 建構分頁器與列表.
     *
     * @param int   $page 頁數
     * @param mixed $query
     *
     * @return mixed
     */
    function pBuildPaginate($page, $query)
    {
        $paginate = 15; //每頁數量
        $skip = ($page * $paginate) - $paginate;
        $prevUrl = $nextUrl = '';

        if ($skip > 0) {
            $prevUrl = $page - 1;
        }

        $list = $query->skip($skip)->take($paginate)->get();

        if ($list->count() >= $paginate) {
            $nextUrl = $page + 1;
        }

        return [
            'list'    => $list,
            'prevUrl' => $prevUrl,
            'nextUrl' => $nextUrl,
        ];
    }
}

if (!function_exists('pSiteLineNotify')) {
    /**
     * 傳送 Line Notify 給 管理員.
     *
     * @param string $content
     *
     * @return mixed
     */
    function pSiteLineNotify($content)
    {
        (new LineNotifyHandler)
        ->notifyHandle(config('services.line_notify.site'), $content);
    }
}

if (!function_exists('pCacheViewCou')) {
    /**
     * 建立 Cache View Cou.
     *
     * @param string   $slug
     * @param string   $ip
     * @param bool     $clear
     *
     * @return mixed
     */
    function pCacheViewCou($slug, $ip = null, $clear = false)
    {
        if (false === config('cache.cache_redis_enabled') || !$ip ) {
            return;
        }

        $prefix = 'v_' . date('md');
        $key = $prefix . '_' . $slug;
        $redis = Cache::getRedis();

        if ($clear) {
            $redis->del($prefix);
            $redis->del($key);
        }

        if ($redis->sadd($key, $ip)) {
            $redis->hincrby($prefix, $key, 1);
        }
    }
}

if (!function_exists('pCacheDb')) {
    /**
     * 建立 db cache.
     *
     * @param string   $cacheKey
     * @param function $callback
     * @param int|null $seconds  預設 86400秒 (1天), null: 無限
     *
     * @return mixed
     */
    function pCacheDb($cacheKey, $callback, $isHost = false, $seconds = 86400)
    {
        if (false === config('cache.cache_redis_enabled') || $isHost) {
            return $callback();
        }

        if (!$seconds) {
            return Cache::rememberForever($cacheKey, $callback);
        } else {
            return Cache::remember($cacheKey, (int) $seconds, $callback);
        }
    }
}

if (!function_exists('pCacheTags')) {
    /**
     * 建立 site db tags.
     *
     * @param string   $prefix
     * @param string   $cacheKey
     * @param Closure  $callback
     * @param int|null $seconds  預設 259200秒 (3天), null: 無限
     *
     * @return mixed
     */
    function pCacheTags($prefix, $cacheKey, $callback, $seconds = 86400 * 3)
    {
        if (false === config('cache.cache_redis_enabled')) {
            return $callback();
        }

        $data = null;
        if (!$seconds) {
            return $data = Cache::tags($prefix)->rememberForever($cacheKey, $callback);
        } else {
            return $data = Cache::tags($prefix)->remember($cacheKey, (int) $seconds, $callback);
        }
    }
}

if (!function_exists('pCacheIncHash')) {
    /**
     * 建立 site db inc.
     *
     * @param string   $prefix
     * @param string   $key
     * @param int      $cou  預設:1, 0:刪除全部($clear=true)
     * @param bool     $clear
     *
     * @return mixed
     */
    function pCacheIncHash($prefix, $key, $cou = 1, $clear = false)
    {
        if (false === config('cache.cache_redis_enabled')) {
            return;
        }

        $cacheKey = $prefix;
        $redis = Cache::getRedis();

        if ($clear) {
            if ($cou === 0) {
                $redis->hgetall($cacheKey);
            } else {
                $redis->hdel($cacheKey, $key);
            }
        }
        $redis->hincrby($cacheKey, $key, $cou);
    }
}

/*
 * 清除 cache by key
 */
if (!function_exists('pForgetCache')) {
    /**
     * 清除 cache by key.
     *
     * @param string|array $key
     *
     * @return void
     */
    function pForgetCache($key)
    {
        $cacheKeys = [];
        if (!is_array($key)) {
            $cacheKeys = [$key];
        } else {
            $cacheKeys = $key;
        }

        foreach ($cacheKeys as $k) {
            $cacheKey = $k;
            Cache::forget($cacheKey);
        }
    }
}
