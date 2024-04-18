<?php

namespace App\Console\Commands;

use App\Enums\Blockchain;
use App\Enums\CurrencySymbol;
use App\Enums\RateSource;
use App\Models\Store;
use App\Models\WithdrawalWallet;
use App\Services\Currency\CurrencyRateService;
use App\Services\Exchange\ExchangeManager;
use App\Services\Explorer\Public\Bitcoin;
use App\Services\Explorer\Public\Ethereum;
use App\Services\Explorer\Public\Tron;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WalletBalancesWithdrawalActualization extends Command
{
    protected $signature = 'wallet:balances:withdrawal-actualization';

    protected $description = 'Actualization withdrawal wallet balances.';

    public function __construct(
        protected CurrencyRateService $currencyService,
        protected ExchangeManager     $exchangeManager,
        protected Bitcoin             $btcExplorer,
        protected Ethereum            $ethExplorer,
        protected Tron                $trxExplorer,
    )
    {
        parent::__construct();
    }

    public function handle(): void
    {

        $stores = Store::all();
        $exchangeWalletsBalanceUsd = [];
        $custodialWalletsBalanceUsd = [];

        foreach ($stores as $store) {
            if (!$store->user) {
                continue;
            }

            $exchangeWalletsBalanceUsd[$store->user->id] = $exchangeWalletsBalanceUsd[$store->user->id] ?? 0.0;
            $custodialWalletsBalanceUsd[$store->user->id] = $custodialWalletsBalanceUsd[$store->user->id] ?? 0.0;

            $exchangeWalletsBalanceUsd[$store->user->id] += $this->getExchangeWalletsUsdBalance($store);
            $custodialWalletsBalanceUsd[$store->user->id] += $this->getCustodialWalletsUsdBalance($store);

        }

        foreach ($exchangeWalletsBalanceUsd as $userId => $walletBalance) {
            Cache::set(
                'exchange-wallets-balance-usd-user-' . $userId,
                $walletBalance
            );
        }

        foreach ($custodialWalletsBalanceUsd as $userId => $walletBalance) {
            Cache::set(
                'custodial-wallets-balance-usd-user-' . $userId,
                $walletBalance
            );
        }

    }

    private function getExchangeWalletsUsdBalance(Store $store): float
    {
        $exchangeBalanceUsdt = 0.0;

        try {
            $huobiExchangeService = $this->exchangeManager->setUser($store->user)
                ->driver('huobi');
            $balances = $huobiExchangeService->getExchangeBalance();
        } catch (\Exception $e) {
            Log::info('Bad response from exchange or keys not configured');
            return $exchangeBalanceUsdt;
        }

        $balances = $balances->whereIn('currency', CurrencySymbol::casesToLower())
            ->where('type', 'trade')
            ->where('balance', '>', 0)
            ->values();

        foreach ($balances as $balance) {

            $usdt = (float)$this->currencyService->inUsd(
                RateSource::fromStore($store),
                CurrencySymbol::tryFromLower($balance->currency),
                CurrencySymbol::USDT,
                $balance->balance,
                true
            );
            $exchangeBalanceUsdt += $usdt;
        }

        return $exchangeBalanceUsdt;
    }

    private function getCustodialWalletsUsdBalance(Store $store): float
    {
        $custodialWallets = WithdrawalWallet::query()
            ->where('user_id', $store->user->id)
            ->whereNull('exchange_id')
            ->get();

        $custodialWalletsBalance = 0.0;

        foreach ($custodialWallets as $custodialWallet) {
            foreach ($custodialWallet->address as $custodialWalletAddress) {
                $custodialWalletsBalance += $this->getCustodialWalletUsdBalance(
                    $custodialWallet->blockchain,
                    $custodialWalletAddress->address,
                    $store
                );
            }
        }

        return $custodialWalletsBalance;
    }

    private function getCustodialWalletUsdBalance(Blockchain $blockchain, string $address, Store $store): float
    {
        return match ($blockchain) {
            Blockchain::Ethereum => (float)$this->ethExplorer->getAddressBalance($address),
            Blockchain::Tron => (float)$this->trxExplorer->getAddressBalance($address),
            Blockchain::Bitcoin => (float)$this->currencyService->inUsd(
                rateSource: RateSource::fromStore($store),
                from: CurrencySymbol::BTC,
                to: CurrencySymbol::USDT,
                amount: $this->btcExplorer->getAddressBalance($address),
                reverseRate: true
            ),
            default => 0.0,
        };
    }

}
