<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('queue:work --sleep=3 --tries=3 --queue=high --timeout=600 --max-jobs=10')->everyMinute()->sendOutputTo(storage_path() . '/logs/queue-jobs.log')->withoutOverlapping();
        $schedule->command('queue:work --sleep=3 --tries=3 --queue=default --timeout=600 --max-jobs=10')->everyMinute()->sendOutputTo(storage_path() . '/logs/queue-jobs.log')->withoutOverlapping();
        $schedule->call(function () {
            // Your task logic here
            Log::info('Cron job executed at ' . now());
        })->everyMinute();
         // $schedule->call('App\Http\Controllers\Admin\LoginController@forcepassword_change')->cron('* * * * *');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
