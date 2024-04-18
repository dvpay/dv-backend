<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('cache:currency:rate')
            ->withoutOverlapping()
            ->everyFiveMinutes();


        $schedule->command('system:status:update')
            ->withoutOverlapping()
            ->everyMinute();


        $schedule->command('processing:resource:actualization')
            ->withoutOverlapping()
            ->everyMinute();
        /**
         * Monitoring
         */
        $schedule->command('processing:status:check')
            ->withoutOverlapping()
            ->everyFiveMinutes();

        $schedule->command('exchange')
            ->withoutOverlapping()
            ->everyMinute();

        $schedule->command('withdrawal')
            ->withoutOverlapping()
            ->everyMinute();

	    $schedule->command('service:log:clear')
             ->withoutOverlapping()
             ->daily();

        $schedule->command('disk:free:check')
            ->withoutOverlapping()
            ->everyFiveMinutes();

        $schedule->command('expired:transfer')
            ->withoutOverlapping()
            ->everyTenMinutes();

        $schedule->command('unconfirmed:check')
            ->withoutOverlapping()
            ->everyFiveMinutes();

        $schedule->command('wallet:balances:withdrawal-actualization')
            ->withoutOverlapping()
            ->everyFiveMinutes();

        $schedule->command('cold:wallet:balance')
            ->withoutOverlapping()
            ->everyMinute();

        /*
         * Report send stats
         * */

        $schedule->command('report dailyReport')
            ->withoutOverlapping()
            ->dailyAt('10:00');

        $schedule->command('report weeklyReport')
                ->withoutOverlapping()
                ->weeklyOn(1, ('10:00'));

        $schedule->command('report monthlyReport')
                ->withoutOverlapping()
                ->monthlyOn(1, ('10:00'));

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
