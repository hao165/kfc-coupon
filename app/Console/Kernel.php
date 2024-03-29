<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('count:view')->dailyAt('00:05')->appendOutputTo(storage_path('logs/count_views.log'));
        $schedule->command('sitemap:generate')->dailyAt('02:00');

        // Note: 03:30 PTT 常態性異常略過
        $schedule->command('crawler:check-all-post')->everyTenMinutes()->unlessBetween('03:25', '03:35');
        $schedule->command('track:ptt-notify')->everyFiveMinutes()->unlessBetween('03:25', '03:35');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
