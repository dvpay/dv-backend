<?php

namespace App\Services\Exchange;

use App\Models\User;
use App\Services\Exchange\HuobiExchange\HuobiExchangeClient;
use App\Services\Exchange\HuobiExchange\HuobiExchangeService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Manager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;


class ExchangeManager extends Manager
{
    protected User $user;

    public function getDefaultDriver()
    {
        return $this->config->get('exchange.default');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createHuobiDriver()
    {
        $client = $this->container->get(HuobiExchangeClient::class);

        return HuobiExchangeService::make(
            huobiClient: $client,
            user: $this->user,
        );
    }

    public function setUser(User|Authenticatable $user): self
    {
        $this->user = $user;
        return $this;
    }
}