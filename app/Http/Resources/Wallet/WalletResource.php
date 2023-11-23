<?php

declare(strict_types=1);

namespace App\Http\Resources\Wallet;

use App\Enums\Blockchain;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Exchange\UserPairsCollection;
use App\Http\Resources\Exchange\WalletCurrencyCollection;
use App\Http\Resources\Exchange\WalletCurrencyResource;
use App\Models\ExchangeUserPairs;
use Illuminate\Http\Request;

class WalletResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $exchangeColdWallets = $this->exchangeColdWallets->first();

        $userPairs = ExchangeUserPairs::where('user_id', $this->user_id)
            ->where('currency_from', $this->blockchain->getCurrency())
            ->get();

        return [
            'id'                           => $this->id ?? null,
            'address'                      => $this->address,
            'blockchain'                   => $this->blockchain,
            'chain'                        => $this->chain,
            'enabled'                      => $this->withdrawal_enabled ?? false,
            'interval'                     => $this->interval ?? 0,
            'minBalance'                   => $this->minBalance ?? 0,
            'enableAutomaticExchange'      => $this->enable_automatic_exchange,
            'exchange'                     => $this->exchange->name ?? null,
            'exchangeCurrencies'           => WalletCurrencyResource::make($this->exchangeWalletCurrency),
            'exchangeCurrenciesUserList'   => UserPairsCollection::make($userPairs),
            'exchangeColdWalletIsEnabled'  => $exchangeColdWallets?->is_withdrawal_enabled ?: false,
            'exchangeColdWalletAddresses'  => $this->exchangeColdWallets ? ColdWalletCollection::make($this->exchangeColdWallets) : null,
            'exchangeColdWalletMinBalance' => $exchangeColdWallets?->withdrawal_min_balance ?: 0.00,
            'exchangeChain'                => $exchangeColdWallets?->chain ?: null,
            'withdrawalIntervalCron'       => $this->withdrawal_interval_cron,
        ];
    }
}