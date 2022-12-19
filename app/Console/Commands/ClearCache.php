<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class ClearCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清除 cache，並執行 optimize (包含redis)';

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
        $this->info(' - Start - ');

        $this->info('all cache flush..');
        Cache::flush();

        $this->info('清除 optimize..');
        Artisan::call('optimize:clear');

        $this->info(' - End - ');
    }
}
