<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Balance\BalanceService;
use App\Services\Processing\Contracts\ProcessingWalletContract;
use App\Services\Report\ReportService;
use Illuminate\Support\ServiceProvider;

class ReportServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->app->bind(ReportService::class, fn() => new ReportService(
            $this->app->get(BalanceService::class),
            $this->app->get(ProcessingWalletContract::class)
        ));
    }
}
