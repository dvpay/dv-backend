<?php

namespace App\Providers;

use App\Services\Exchange\HuobiExchange\HuobiExchangeClient;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\ServiceProvider;

class HuobiExchangeClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            HuobiExchangeClient::class,
            fn($app) => new HuobiExchangeClient(
                $app->make(PendingRequest::class),
                config('exchange.huobi')
            )
        );
    }

    public function boot(): void
    {
    }
}
