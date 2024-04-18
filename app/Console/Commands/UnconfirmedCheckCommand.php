<?php

namespace App\Console\Commands;

use App\Enums\Blockchain;
use App\Enums\CurrencySymbol;
use App\Enums\RateSource;
use App\Exceptions\ApiException;
use App\Models\Store;
use App\Models\WithdrawalWallet;
use App\Services\Currency\CurrencyRateService;
use App\Services\Explorer\Public\Bitcoin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class UnconfirmedCheckCommand extends Command
{
    protected $signature = 'unconfirmed:check';

    protected $description = 'Command description';

    public function handle(
        CurrencyRateService $currencyService
    ): void
    {
        $stores = Store::all();
        $btcExplorer = new Bitcoin();

        foreach ($stores as $store) {
            if (!$store->user) {
                continue;
            }
            $wallets = WithdrawalWallet::where('user_id', $store->user->id)->where('blockchain', Blockchain::Bitcoin->value)
                ->with('address')
                ->get();

            $exchangeSum = 0;
            $errors = 0;

            foreach ($wallets as $wallet) {
                foreach ($wallet->address as $address) {
                    try {
                        $transactions = $btcExplorer->getAddressTransaction($address->address);
                        foreach ($transactions as $transaction) {
                            $exchangeSum += $transaction['amount'] ?? 0;
                        }
                    } catch (ApiException) {
                        $errors++;
                    }
                    if ($errors) {
                        continue;
                    }
                    sleep(10);
                }
            }
            $btc = $exchangeSum / 100000000;
            $usd = $currencyService->inUsd(RateSource::fromStore($store), CurrencySymbol::BTC, CurrencySymbol::USDT, (string)$btc, true);

            $this->info($btc);
            $this->info($usd);

            Cache::set(sprintf('unconfirmed-%s', $store->user->id), [
                'btc' => (string)$btc,
                'usd' => $usd,
            ]);

        }

    }
}
