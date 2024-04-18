<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Dto\HotWallet\HotWalletDto;
use App\Enums\CurrencyId;
use App\Enums\CurrencySymbol;
use App\Enums\HotWalletState;
use App\Enums\RateSource;
use App\Models\Currency;
use App\Models\User;
use App\Services\Currency\CurrencyConversion;
use App\Services\Currency\CurrencyRateService;
use App\Services\HotWallet\HotWalletService;
use App\Services\Processing\BalanceGetter;
use Illuminate\Console\Command;

class SyncHotWalletsCommand extends Command
{
    protected $signature = 'sync:hot:wallets';

    protected $description = 'Command description';

    public function handle(
        BalanceGetter       $balanceGetter,
        HotWalletService    $hotWalletService,
        CurrencyRateService $currencyRateService,
        CurrencyConversion  $currencyConversion): void
    {
        User::whereNotNull('processing_owner_id')->each(
            callback: function ($user) use (
                $balanceGetter,
                $hotWalletService,
                $currencyRateService,
                $currencyConversion,
            ) {
                $addresses = $balanceGetter->getAddressBalanceByOwnerId(ownerId: $user->processing_owner_id);
                foreach ($addresses as $address) {
                    $address = (object)$address;
                    $currency = Currency::where('blockchain', $address->blockchain)
                        ->where('has_balance', true)
                        ->first();

                    $data = $currencyRateService->getCurrencyRate(
                        RateSource::Binance,
                        CurrencySymbol::USD,
                        $currency->code,
                    );

                    $amount = $currencyConversion->convert(
                        amount: strval($address->balance),
                        rate: $data['rate'],
                    );

                    $dto = new HotWalletDto([
                        'currencyId' => CurrencyId::tryFrom($currency->id),
                        'user'       => $user,
                        'address'    => $address->address,
                        'blockchain' => $currency->blockchain,
                        'amount'     => $address->balance,
                        'amountUsd'  => (float)$amount,
                    ]);

                    $hotWalletService->updateOrCreate($dto);
                }
            });
    }
}
