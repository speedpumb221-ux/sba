<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // analyze road events every minute
        $schedule->command('road-events:analyze')->everyMinute()->withoutOverlapping();

        // import OSM bumps daily at 03:00 (default center Riyadh) - adjust as needed
        $schedule->command('osm:import-bumps')->dailyAt('03:00')->withoutOverlapping();
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
