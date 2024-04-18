<?php

namespace App\Providers;

use App\Jobs\PaymentCallbackJob;
use App\Services\Currency\CurrencyConversion;
use App\Services\Currency\CurrencyRateService;
use App\Services\HotWallet\HotWalletServiceInterface;
use App\Services\Invoice\InvoiceAddressCreator;
use App\Services\Invoice\InvoiceCreator;
use Illuminate\Support\ServiceProvider;

class WebhookServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {

        $this->app->bindMethod([PaymentCallbackJob::class, 'handle'], function ($job, $app) {
            return $job->handle(
                config('processing.min_transaction_confirmations'),
                $app->get(InvoiceCreator::class),
                $app->get(InvoiceAddressCreator::class),
                $app->get(CurrencyRateService::class),
                $app->get(CurrencyConversion::class),
                $app->get(HotWalletServiceInterface::class)
            );
        });

    }
}
