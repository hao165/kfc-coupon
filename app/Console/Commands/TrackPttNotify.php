<?php

namespace App\Console\Commands;

use App\Models\TrackItem;
use Illuminate\Console\Command;
use App\Services\Api\LineNotifyHandler;
use App\Services\TrackHandler;

class TrackPttNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'track:ptt-notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '追蹤看板，並執行Line Notify';

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

        $result = (new TrackHandler())->trackHandle();
        $this->info('符合條件：'.$result);


        $this->info('[1] 開始執行Notify..');
        $count = 0;
        $list = TrackItem::where('status', '=', '0')->get();
        $list->each(function ($item, $key) use (&$count) {
            $token = $item->user->line_notify;
            $text = "{$item->type_name}\n\n看板：{$item->cls}\n\n{$item->title}\n\n{$item->url}";

            $result = (new LineNotifyHandler())->send($token, $text);
            if ($result) {
                $count++;
                $item->status = 1;
                $item->save();
            }
        });

        $this->info('發送筆數：' . $count);
    }
}
