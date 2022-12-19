<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CrawlerHandler;

class CrawlerCheckAllPost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawler:check-all-post';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '爬蟲：執行抓取文章列表的最新回應';

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
        $result = (new CrawlerHandler())->checkAllPost();
        $this->info($result);
    }
}
