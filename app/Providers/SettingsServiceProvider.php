<?php

namespace App\Providers;

use App\Facades\Accessors\SettingsAccessor;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('settings', function ($app) {
            return new SettingsAccessor(Auth::user());
        });
    }

    public function boot(): void
    {
    }
}
