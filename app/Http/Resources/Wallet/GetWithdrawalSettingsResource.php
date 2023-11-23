<?php

declare(strict_types=1);

namespace App\Http\Resources\Wallet;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * GetWithdrawalSettingsResource
 */
class GetWithdrawalSettingsResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'address'                      => $this->address,
            'blockchain'                   => $this->blockchain,
            'enabled'                      => $this->enabled,
            'interval'                     => $this->interval,
            'minBalance'                   => $this->minBalance,
            'enableAutomaticExchange'      => $this->enableAutomaticExchange ?? null,
            'exchange'                     => $this->exchange ?? null,
            'exchangeCurrencies'           => $this->exchangeCurrencies ?? null,
            'exchangeColdWalletIsEnabled'  => $this->exchangeColdWalletIsEnabled ?? false,
            'exchangeColdWalletAddresses'  => $this->exchangeColdWalletAddresses ? ColdWalletCollection::make($this->exchangeColdWalletAddresses) : null,
            'exchangeColdWalletMinBalance' => $this->exchangeColdWalletMinBalance ?? null,
            'exchangeChain'                => $this->exchangeChain ?? null,
            'withdrawalIntervalCron'       => $this->withdrawalIntervalCron ?? null,
        ];
    }
}