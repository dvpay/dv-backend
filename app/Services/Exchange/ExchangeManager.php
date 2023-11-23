<?php

namespace App\Services\Exchange;

use App\Enums\ExchangeService as ExchangeServiceEnum;
use App\Exceptions\ApiException;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Client\PendingRequest;

class ExchangeManager implements ExchangeManagerInterface
{
    private mixed $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * @throws \Exception
     */
    public function make(ExchangeServiceEnum $service, Authenticatable|User $user)
    {
        $exchangeName = $service->getTitle();
        return match ($exchangeName) {
            'Huobi' => $this->createHuobiExchangeService($user),
            default => throw new ApiException("Exchange $exchangeName is not supported", 403),
        };
    }

    private function createHuobiExchangeService(Authenticatable|User $user)
    {
        $config = $this->app['config']['exchange.huobi'];
        $service = HuobiExchangeService::make(app(PendingRequest::class));
        $service->setConfig($config);
        $service->setUser($user);
        $service->setKeys();
        return $service;
    }
}