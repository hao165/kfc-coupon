<?php

namespace App\Console\Commands;

use App\Models\Coupon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CountView extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'count:view {--date= : md.預設為昨日}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '將redis上昨日的查看數轉入DB。';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = $this->option('date') ?? date("md", strtotime("-1 day"));
        if (!is_numeric($date) || strlen($date) !== 4) {
            $this->handleMsg('日期異常：date參數應為4碼。', 'error');
            return false;
        }

        $this->handleMsg(sprintf(' - %s - ', $date));

        $redis  = Cache::getRedis();
        $prefix = sprintf('v_%s', $date);
        $list   = $redis->hgetall($prefix);
        if (count($list) === 0) {
            $this->handleMsg('views not found..', 'error');
            return false;
        }

        $slugs[] = $prefix;
        $total   = 0;
        collect($list)->sortDesc()->each(function ($cou, $key) use (&$slugs, &$total) {
            $temp = Str::of($key)->explode('_');
            $slug = $temp[2];
            if ($cou > 2) {
                Coupon::where('slug', $slug)->increment('view_cou', $cou);
                $this->handleMsg(sprintf('[1] %s ，新增 %s 人次', $slug, $cou));
            }
            $slugs[] = $key;
            $total += $cou;
        });

        $this->handleMsg(sprintf('[2] 總計 %s 人次', $total));

        $redis->del($slugs);
        $this->handleMsg('[3] 已清除 redis cache views count..');
    }

    /**
     * console log.
     *
     * @param string $msg
     * @param string $type info|error
     */
    public function handleMsg($msg, $type = 'info')
    {
        $this->$type($msg);
        // Log::channel('count_view')->$type($msg);
    }
}
