<?php

namespace App\Console\Commands;

use App\Models\TrackItem;
use Illuminate\Console\Command;
use App\Services\LineNotifyHandler;
use App\Services\TrackHandler;

class TrackPttNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'track:pttNotify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '追蹤看板 並Line Notify';

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
     * @return int
     */
    public function handle()
    {
        $this->info('[0] 開始爬取文章..');

        $TrackHandler  = new TrackHandler();
        $result = $TrackHandler->trackHandle();
        $this->info('符合條件：'.$result);

        $this->info('[1] 開始執行Notify..');
        $list = TrackItem::where('status', '=', '0')->get();
        $cou = 0;
        foreach ($list as $item) {
            $token = $item->user->line_notify;
            $text = "{$item->type_name}\n\n看板：{$item->cls}\n\n{$item->title}\n\n{$item->url}";

            $LineNotifyHandler  = new LineNotifyHandler();
            $result = $LineNotifyHandler->notifyHandle($token, $text);

            if ($result) {
                $cou++;
                $item->status = 1;
                $item->save();
            }
        }

        $this->info('發送筆數：' . $cou);
    }
}
