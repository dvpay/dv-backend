<?php

namespace App\Providers;

use App\Services\Exchange\ExchangeManager;
use App\Services\Exchange\ExchangeManagerInterface;
use Illuminate\Support\ServiceProvider;

class ExchangeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ExchangeManagerInterface::class, fn($app) => new ExchangeManager($app));
    }

    public function boot(): void
    {
    }
}
