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
        // Generate sitemap daily at midnight
        $schedule->command('sitemap:generate')
            ->daily()
            ->at('00:00')
            ->onSuccess(function () {
                info('Sitemap generated successfully');
            })
            ->onFailure(function () {
                info('Sitemap generation failed');
            });
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
