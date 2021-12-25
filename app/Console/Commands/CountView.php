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
    protected $description = '將前一天查看數，轉入DB。';

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
        $log = 'count_view';
        $date = $this->option('date');
        if (!$date) {
            $date = date("md", strtotime("-1 day"));
        }
        if (!is_numeric($date) || strlen($date) != 4) {
            $msg = '輸入日期異常。應為md，四碼';
            $this->error($msg);
            // Log::channel($log)->info($msg);
            return false;
        }
        $msg = ' - ' . $date . ' - ';
        $this->info($msg);
        // Log::channel($log)->info($msg);

        $prefix = 'v_' . $date;
        $redis = Cache::getRedis();
        $list = $redis->hgetall($prefix);
        if(count($list)==0){
            $msg = '找不到紀錄。';
            $this->error($msg);
            // Log::channel($log)->info($msg);
            return false;
        }

        $slugs[] = $prefix;
        $total = 0;
        foreach ($list as $key => $cou) {
            $temp = Str::of($key)->explode('_');
            $slug = $temp[2];
            if ($cou > 2) {
                $msg = '[1] ' . $slug . ' ，新增 ' . $cou . ' 人次';
                $this->info($msg);
                // Log::channel($log)->info($msg);
                Coupon::where('slug',$slug)->increment('view_cou', $cou);
            }
            $slugs[] = $key;
            $total += $cou;
        }

        $msg = '[2] 總計 ' . $total . ' 人次';
        $this->info($msg);
        // Log::channel($log)->info($msg);

        $redis->del($slugs);
        $msg = '[3] 已清除 redis cache views count..';
        $this->info($msg);
        // Log::channel($log)->info($msg);


        // $msg = ' - end - ';
        // $this->info($msg);
        // Log::channel($log)->info($msg);
    }
}
