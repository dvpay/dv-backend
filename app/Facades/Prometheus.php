<?php

namespace App\Facades;

use App\Services\Prometheus\FakePrometheusExporter;
use Illuminate\Support\Facades\Facade;
class Prometheus extends Facade
{

    public static function fake(): void
    {
        static::swap(new FakePrometheusExporter());
    }

    protected static function getFacadeAccessor(): string
    {
        return 'prometheus';
    }
}