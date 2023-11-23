<?php

namespace App\Providers;

use App\Interfaces\RateSource as RateSourceInterface;
use App\ServiceLocator\RateSourceLocator;
use App\Services\Currency\CurrencyStore;
use App\Services\RateSource\Binance;
use App\Services\RateSource\CoinGate;
use App\Services\RateSource\LoadRateFake;
use Illuminate\Support\ServiceProvider;

class LocatorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (config('setting.rate_source_fake')) {
            $this->app->bind(RateSourceLocator::class, fn() => new RateSourceLocator(
                $this->app->get(LoadRateFake::class)
            ));
        } else {
            $this->app->bind(RateSourceLocator::class, fn () => new RateSourceLocator(
                $this->app->get(CoinGate::class),
                $this->app->get(Binance::class)
            ));
        }
    }
}
