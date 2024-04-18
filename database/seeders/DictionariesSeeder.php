<?php

namespace Database\Seeders;

use App\Enums\Blockchain;
use App\Enums\CurrencySymbol;
use App\Enums\ExchangeKeyType;
use App\Enums\ExchangeService;
use App\Enums\RateSource as RateSourceEnum;
use App\Models\Currency;
use App\Models\Exchange;
use App\Models\ExchangeDictionary;
use App\Models\ExchangeKey;
use App\Models\RateSource;
use Illuminate\Database\Seeder;
use Throwable;

class DictionariesSeeder extends Seeder
{
    private array $currencies = [
        [
            'id' => 'USD',
            'name' => CurrencySymbol::USD,
            'code' => CurrencySymbol::USD,
            'contract_address' => '',
            'precision' => 2,
            'is_fiat' => true,
            'has_balance' => false,
        ],
        [
            'id' => 'BTC.Bitcoin',
            'name' => CurrencySymbol::BTC,
            'code' => CurrencySymbol::BTC,
            'blockchain' => Blockchain::Bitcoin,
            'chain' => 'btc',
            'contract_address' => '',
            'precision' => 8,
            'is_fiat' => false,
            'withdrawal_min_balance' => 0.00030000,
            'sort_order' => 2
        ],
        [
            'id' => 'USDT.Tron',
            'name' => CurrencySymbol::USDT,
            'code' => CurrencySymbol::USDT,
            'contract_address' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
            'blockchain' => Blockchain::Tron,
            'chain' => 'trc20usdt',
            'precision' => 2,
            'is_fiat' => false,
            'withdrawal_min_balance' => 0.5,
            'sort_order' => 1
        ],
        [
            'id' => 'USDT.ETH',
            'name' => CurrencySymbol::USDT,
            'code' => CurrencySymbol::USDT,
            'contract_address' => '0xdAC17F958D2ee523a2206206994597C13D831ec7',
            'blockchain' => Blockchain::Ethereum,
            'chain' => 'usdterc20',
            'precision' => 2,
            'is_fiat' => false,
            'has_balance' => false,
            'withdrawal_min_balance' => 10,
        ],
        [
            'id' => 'TRX.Tron',
            'name' => CurrencySymbol::TRX,
            'code' => CurrencySymbol::TRX,
            'contract_address' => '',
            'blockchain' => Blockchain::Tron,
            'chain' => 'trx',
            'precision' => 6,
            'is_fiat' => false,
            'withdrawal_min_balance' => 10,
            'has_balance' => false,
            'sort_order' => 3
        ],
        [
            'id' => 'ETH.Ethereum',
            'name' => CurrencySymbol::ETH,
            'code' => CurrencySymbol::ETH,
            'contract_address' => '',
            'blockchain' => Blockchain::Ethereum,
            'chain' => 'eth',
            'precision' => 6,
            'is_fiat' => false,
            'withdrawal_min_balance' => 10,
            'has_balance' => false,
        ],
    ];

    /**
     * @throws Throwable
     */
    public function run()
    {
        foreach ($this->currencies as $currency) {
            if (!$existCurrency = Currency::where('id', $currency['id'])->first()) {
                Currency::factory()->create($currency);
            } else {
                $existCurrency->update($currency);
            }
        }

        $rateSources = RateSourceEnum::cases();
        foreach ($rateSources as $rateSource) {
            if (!$existRateSource = RateSource::where('name', $rateSource)->first()) {
                RateSource::factory()->create([
                    'name' => $rateSource,
                    'uri' => $rateSource->getUri(),
                ]);
            } else {
                $existRateSource->update([
                    'name' => $rateSource,
                    'uri' => $rateSource->getUri(),
                ]);
            }
        }

        $exchangeServices = ExchangeService::cases();
        foreach ($exchangeServices as $exchangeService) {
            if ($service = Exchange::find($exchangeService->getId())) {
                $service->name = $exchangeService->getTitle();
                $service->slug = $exchangeService;
                $service->url = $exchangeService->getUrl();
            } else {
                $service = new Exchange([
                    'id' => $exchangeService->getId(),
                    'name' => $exchangeService->getTitle(),
                    'slug' => $exchangeService,
                    'url' => $exchangeService->getUrl(),
                    'is_active' => $exchangeService->value == 'huobi',
                ]);
            }

            $service->saveOrFail();
        }

        $exchanges = Exchange::all();
        foreach ($exchanges as $exchange) {
            $exchangeKeyTypes = ExchangeKeyType::cases();
            foreach ($exchangeKeyTypes as $exchangeKeyType) {
                $exchangeKey = ExchangeKey::where([
                    ['exchange_id', $exchange->id],
                    ['key', $exchangeKeyType],
                ])->first();

                if ($exchangeKey) {
                    $exchangeKey->key = $exchangeKeyType;
                } else {
                    $exchangeKey = new ExchangeKey([
                        'exchange_id' => $exchange->id,
                        'key' => $exchangeKeyType,
                    ]);
                }

                $exchangeKey->saveOrFail();
            }
        }

        $exchangeDictionary = ExchangeDictionary::where('exchange_id', ExchangeService::Huobi->getId())->first();
        if ($exchangeDictionary) {
            $exchangeDictionary->from_currency_id = 'BTC.Bitcoin';
            $exchangeDictionary->to_currency_id = 'USDT.Tron';
            $exchangeDictionary->decimals = 6;
        } else {
            $exchangeDictionary = new ExchangeDictionary([
                'exchange_id' => ExchangeService::Huobi->getId(),
                'from_currency_id' => 'BTC.Bitcoin',
                'to_currency_id' => 'USDT.Tron',
                'min_quantity' => '0.0001', // from https://api.huobi.pro/v1/common/symbols
                'decimals' => 6, // from https://api.huobi.pro/v1/common/symbols
            ]);
        }

        $exchangeDictionary->saveOrFail();
    }
}
