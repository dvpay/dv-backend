<?php

namespace App\Http\Controllers\Api\External;

use App\Enums\CurrencySymbol;
use App\Enums\ExchangeService as ExchangeServiceEnum;
use App\Enums\RateSource;
use App\Exceptions\ServiceUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Resources\DefaultResponseResource;
use App\Models\ExchangeWithdrawalWallet;
use App\Repositories\StoreRepository;
use App\Services\Currency\CurrencyConversion;
use App\Services\Currency\CurrencyRateService;
use App\Services\Exchange\ExchangeManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ExchangeController extends Controller
{
    public function __invoke(
        Request             $request,
        StoreRepository     $storeRepository,
        ExchangeManager     $exchangeManager,
        CurrencyRateService $currencyRateService,
        CurrencyConversion  $currencyConversion
    )
    {
        $store = $storeRepository->getStoreByApiKey($request->header('X-Api-Key'));

        if (!$store->status) {
            throw new ServiceUnavailableException(message: "Store inactive");
        }
        $service = $exchangeManager->setUser($store->user)->driver('huobi');

        $withdrawalAddress = ExchangeWithdrawalWallet::where('user_id', $store->user->id)
            ->get()
            ->map(fn($item) => [
                'address' => $item->address,
                'balance' => $item->current_balance
            ]);

        $unconfirmedBitcoin = Cache::get(sprintf('unconfirmed-%s', $store->user->id));

        try {
            $balance = $service->getExchangeBalance()
                ->whereIn('currency', ['usdt', 'btc'])
                ->where('type', 'trade')
                ->values();

            foreach ($balance as $item) {
                $data = $currencyRateService->getCurrencyRate(
                    RateSource::Binance,
                    CurrencySymbol::USD,
                    CurrencySymbol::from(Str::upper($item->currency)),
                );
                $amount = $currencyConversion->convert(
                    amount: strval($item->balance),
                    rate: $data['rate'],
                );
                $item->usdt = $amount;
            }
        } catch (\Throwable $exception) {
            $balance = [];
        }


        return new DefaultResponseResource([
            'address'            => $withdrawalAddress,
            'balance'            => $balance,
            'unconfirmedBitcoin' => $unconfirmedBitcoin
        ]);

    }
}
