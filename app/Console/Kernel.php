<?php

namespace App\Console;

use App\Enums\WithdrawalInterval;
use App\Jobs\NewTransferJob;
use App\Jobs\TransferJob;
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

        $schedule->command('invoice:update:status')
            ->withoutOverlapping()
            ->everyMinute();

        $schedule->command('system:status:update')
            ->withoutOverlapping()
            ->everyMinute();

        $schedule->command('user:invoice:address:update')
            ->withoutOverlapping()
            ->everyMinute();


        /**
         * Monitoring
         */
        $schedule->command('processing:status:check')
            ->withoutOverlapping()
            ->everyFiveMinutes();

        $schedule->command('explorer:status:check')
            ->withoutOverlapping()
            ->everyFiveMinutes();

        $schedule->command('node:status:check')
            ->withoutOverlapping()
            ->everyFiveMinutes();

        $schedule->command('node:version:control')
            ->withoutOverlapping()
            ->everySixHours();

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

        /*
         *  Withdrawal and transfer
         * */
//        foreach (WithdrawalInterval::cases() as $interval) {
//            $schedule->job(new NewTransferJob($interval))
//                ->withoutOverlapping()
//                ->cron($interval->value);
//        }
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
