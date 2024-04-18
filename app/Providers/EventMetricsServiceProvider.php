<?php

namespace App\Providers;

use App\Enums\Metric;
use App\Facades\Prometheus;
use App\Models\Transaction;
use App\Models\UnconfirmedTransaction;
use App\Observers\TransactionObserver;
use App\Observers\UnconfirmedTransactionObserver;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

/**
 * EventServiceProvider
 */
class EventMetricsServiceProvider extends ServiceProvider
{

    private float $command_start_time = 0.0;

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(CommandStarting::class, function (CommandStarting $event) {
            $this->command_start_time = microtime(true);
        });

        Event::listen(CommandFinished::class, function (CommandFinished $event) {
            $executionTime = microtime(true) - $this->command_start_time;
            $command = $event->command ?? 'unknown';
            $exitCode = $event->exitCode ?? -1;

            Prometheus::histogramObserve(
                Metric::BackendCommandExecutionDurationTime->getName(),
                $executionTime,
                [$command, $exitCode]
            );
        });

        Event::listen(ScheduledTaskFinished::class, function (ScheduledTaskFinished $event) {
            $executedCommand = $this->parseCommandFromTask($event->task->command);
            $executionTime = $event->runtime;

            Prometheus::histogramObserve(
                Metric::BackendScheduledCommandExecutionDurationTime->getName(),
                $executionTime,
                [$executedCommand]
            );

        });

        Event::listen(ScheduledTaskFailed::class, function (ScheduledTaskFailed $event) {

            $executedCommand = $this->parseCommandFromTask($event->task->command);

            Prometheus::counterInc(
                name: Metric::BackendScheduledFailedAndSkippedCommands->getName(),
                labels: [$executedCommand, 'failed']
            );

        });

        Event::listen(ScheduledTaskSkipped::class, function (ScheduledTaskSkipped $event) {

            $executedCommand = $this->parseCommandFromTask($event->task->command);

            Prometheus::counterInc(
                name: Metric::BackendScheduledFailedAndSkippedCommands->getName(),
                labels: [$executedCommand, 'skipped']
            );

        });

        Transaction::observe(TransactionObserver::class);

        UnconfirmedTransaction::observe(UnconfirmedTransactionObserver::class);

    }

    private function parseCommandFromTask(string $task): string
    {
        $executedCommand = 'unknown';

        foreach (array_keys(\Artisan::all()) as $command) {
            if(
                Str::contains($task, $command . ' ') ||
                Str::endsWith($task,$command)
            ) {
                $executedCommand = $command;
            }
        }

        return $executedCommand;
    }
}
