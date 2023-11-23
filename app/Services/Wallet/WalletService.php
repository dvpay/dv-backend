<?php

declare(strict_types=1);

namespace App\Services\Wallet;

use App\Models\Currency;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletBalance;
use App\Repositories\WalletRepository;
use Illuminate\Database\Eloquent\Collection;

class WalletService
{

    /**
     * @param User $user
     * @return Collection
     */
    public function list(User $user): Collection
    {
        return Wallet::where('user_id', $user->id)
            ->with(['exchangeKeys', 'exchangeWalletCurrency', 'exchangeColdWallets', 'exchange'])
            ->get();
    }

    public function createAllBalancesForWallet(Wallet $wallet): void
    {
        $currencies = Currency::where([
            ['blockchain', $wallet->blockchain],
            ['has_balance', true],
        ])->get();
        foreach ($currencies as $currency) {
            $balance = WalletBalance::where([
                'wallet_id' => $wallet->id,
                'currency_id' => $currency->id,
            ])->exists();

            if ($balance) {
                continue;
            }

            $this->createBalance($wallet, $currency);
        }
    }

    public function createBalance(Wallet $wallet, Currency $currency): void
    {
        $balance = new WalletBalance([
            'wallet_id' => $wallet->id,
            'currency_id' => $currency->id,
            'balance' => 0,
        ]);
        $balance->save();
    }
}