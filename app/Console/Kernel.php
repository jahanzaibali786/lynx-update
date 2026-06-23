<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('fix:journal-data-fast')
        ->dailyAt('18:30')  // 4 PM
        ->withoutOverlapping() // Prevents running multiple times if previous run not finished
        ->onOneServer();  

        $schedule->command('student-finance:cleanup --process')
            ->everyTwoMinutes()
            ->withoutOverlapping(10)
            ->onOneServer();

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
