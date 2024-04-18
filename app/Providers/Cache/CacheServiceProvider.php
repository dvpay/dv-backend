<?php

namespace App\Providers\Cache;

use App\Cache\CacheManager;
use Illuminate\Cache\CacheServiceProvider as LaravelCacheServiceProvider;
class CacheServiceProvider extends LaravelCacheServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('cache', function ($app) {
           return New CacheManager($app);
        });

        $this->app->singleton('cache.store', function ($app) {
            return $app['cache']->driver();
        });
    }
}