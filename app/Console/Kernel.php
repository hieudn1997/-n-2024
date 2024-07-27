<?php

namespace App\Console;

use App\Console\Commands\DailyPrice;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\Log;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        DailyPrice::class
    ];
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('price:daily')->dailyAt('17:00');
        // $schedule->command('buy:daily')->dailyAt('14:00');
        $schedule->command('buy:daily')->everyMinute();
        // $schedule->command('sell:daily')->dailyAt('14:00');
        $schedule->command('sell:daily')->everyMinute();
        $schedule->command('payment:check')->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
