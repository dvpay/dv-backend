<?php

namespace Tests\Unit\Exchange;

use App\Enums\CurrencySymbol;
use App\Enums\ExchangeChainType;
use App\Enums\ExchangeKeyType;
use App\Enums\ExchangeService;
use App\Models\Exchange;
use App\Models\ExchangeKey;
use App\Models\ExchangeUserKey;
use App\Models\ExchangeWithdrawalWallet;
use App\Models\User;
use App\Services\Exchange\ExchangeManager;
use App\Services\Exchange\HuobiExchange\HuobiExchangeClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HuobiExchangeServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @dataProvider itPlacesWithdrawalRequestsDataProvider
     */
    public function testItPlacesWithdrawalRequestsToHuobi(
        $balance,
        $chain,
        $currency,
        $exchangeWithdrawalWalletMinBalance,
        $withdrawalAmount
    )
    {

        $user = User::factory()->create();

        $huobiExchangeClientMock = \Mockery::mock(HuobiExchangeClient::class)->makePartial();

        $huobiExchangeClientMock->shouldReceive('getAccounts')
            ->andReturn($this->mockGetAccountsMethodResponse());

        $huobiExchangeClientMock->shouldReceive('getBalance')
            ->andReturn($this->mockGetBalanceMethodResponse(
                balance: $balance,
                currency: $currency
            ));

        $huobiExchangeClientMock->shouldReceive('placeWithdrawal')
            ->andReturn($this->mockPlaceWithdrawalMethodResponse(
                amount: $withdrawalAmount,
                currency: $currency,
                chain: $chain->value,
            ));

        $huobiExchangeClientMock->shouldReceive('getMarketDetail')
            ->andReturnUsing(
                fn($params) => $this->mockGetMarketDetailMethodResponse($params)
            );

        $this->app->instance(HuobiExchangeClient::class, $huobiExchangeClientMock);

        $huobiExchange = Exchange::where('name', ExchangeService::Huobi->getTitle())->first();

        $exchangeWithdrawalWallet = ExchangeWithdrawalWallet::factory()->create([
            'min_balance' => $exchangeWithdrawalWalletMinBalance,
            'chain'       => $chain,
            'currency'    => $currency,
            'user_id'     => $user,
            'exchange_id' => $huobiExchange,
        ]);

        $exchangeAccessKey = ExchangeKey::query()
            ->where('exchange_id', $huobiExchange->id)
            ->where('key', ExchangeKeyType::AccessKey)
            ->first();

        $exchangeSecretKey = ExchangeKey::query()
            ->where('exchange_id', $huobiExchange->id)
            ->where('key', ExchangeKeyType::SecretKey)
            ->first();

        ExchangeUserKey::factory()->create([
            'user_id' => $user,
            'key_id'  => $exchangeAccessKey,
        ]);

        ExchangeUserKey::factory()->create([
            'user_id' => $user,
            'key_id'  => $exchangeSecretKey,
        ]);

        $exchangeManager = app(ExchangeManager::class);

        $exchangeService = $exchangeManager->setUser($exchangeWithdrawalWallet->user)
            ->driver(ExchangeService::Huobi->value);

        $exchangeService->withdrawalFromExchange();

        $this->assertDatabaseHas('exchange_cold_wallet_withdrawals', [
            'exchange_cold_wallet_id' => $exchangeWithdrawalWallet->id,
            'exchange_id'             => $huobiExchange->id,
            'address'                 => $exchangeWithdrawalWallet->address,
            'amount'                  => $withdrawalAmount,

        ]);

    }

    /**
     * @test
     * @dataProvider itDoesNotPlacesWithdrawalRequestsToHuobiOverMinimalBalanceDataProvider
     */
    public function testItDoesNotPlacesWithdrawalRequestsToHuobiOverMinimalBalance(
        $balance,
        $chain,
        $currency,
        $exchangeWithdrawalWalletMinBalance
    )
    {

        $user = User::factory()->create();

        $huobiExchangeClientMock = \Mockery::mock(HuobiExchangeClient::class)->makePartial();

        $huobiExchangeClientMock->shouldReceive('getAccounts')
            ->andReturn($this->mockGetAccountsMethodResponse());

        $huobiExchangeClientMock->shouldReceive('getBalance')
            ->andReturn($this->mockGetBalanceMethodResponse(
                balance: $balance,
                currency: $currency
            ));

        $huobiExchangeClientMock->shouldReceive('getMarketDetail')
            ->andReturnUsing(
                fn($params) => $this->mockGetMarketDetailMethodResponse($params)
            );

        $huobiExchangeClientMock->shouldReceive('placeWithdrawal')
            ->andReturn($this->mockPlaceWithdrawalMethodResponse(
                currency: $currency,
                chain: $chain->value,
            ));

        $this->app->instance(HuobiExchangeClient::class, $huobiExchangeClientMock);

        $huobiExchange = Exchange::where('name', ExchangeService::Huobi->getTitle())->first();

        $exchangeWithdrawalWallet = ExchangeWithdrawalWallet::factory()->create([
            'min_balance' => $exchangeWithdrawalWalletMinBalance,
            'chain'       => $chain,
            'currency'    => $currency,
            'user_id'     => $user,
            'exchange_id' => $huobiExchange,
        ]);

        $exchangeAccessKey = ExchangeKey::query()
            ->where('exchange_id', $huobiExchange->id)
            ->where('key', ExchangeKeyType::AccessKey)
            ->first();

        $exchangeSecretKey = ExchangeKey::query()
            ->where('exchange_id', $huobiExchange->id)
            ->where('key', ExchangeKeyType::SecretKey)
            ->first();

        ExchangeUserKey::factory()->create([
            'user_id' => $user,
            'key_id'  => $exchangeAccessKey,
        ]);

        ExchangeUserKey::factory()->create([
            'user_id' => $user,
            'key_id'  => $exchangeSecretKey,
        ]);

        $exchangeManager = app(ExchangeManager::class);

        $exchangeService = $exchangeManager->setUser($exchangeWithdrawalWallet->user)
            ->driver(ExchangeService::Huobi->value);

        $exchangeService->withdrawalFromExchange();

        $this->assertDatabaseMissing('exchange_cold_wallet_withdrawals', [
            'exchange_cold_wallet_id' => $exchangeWithdrawalWallet->id,
            'exchange_id'             => $huobiExchange->id,
            'address'                 => $exchangeWithdrawalWallet->address,
        ]);

    }

    private function mockGetAccountsMethodResponse()
    {
        return json_decode('
            [
                {
                    "id": 10000001,
                    "type": "spot",
                    "subtype": "",
                    "state": "working"
                },
                {
                    "id": 10000002,
                    "type": "otc",
                    "subtype": "",
                    "state": "working"
                },
                {
                    "id": 10000003,
                    "type": "point",
                    "subtype": "",
                    "state": "working"
                }
            ]
            ');
    }

    private function mockGetBalanceMethodResponse(float $balance = 91.850043797676510303, string $currency = 'usdt')
    {
        return json_decode('
            {
                "id": 1000001,
                "type": "spot",
                "state": "working",
                "list": [
                    {
                        "currency": "' . $currency . '",
                        "type": "trade",
                        "balance": "' . $balance . '",
                        "seq-num": "477"
                    }
                ]
            }
            ');
    }

    private function mockPlaceWithdrawalMethodResponse(
        float  $amount = 1.200000000000000000,
        string $currency = 'usdt',
        string $chain = 'btc',
    )
    {
        return json_decode('
             {
                "status": "ok",
                "data": {
                    "id": 101123262,
                    "client-order-id": "1113",
                    "type": "withdraw",
                    "sub-type": "FAST",
                    "currency": "' . $currency . '",
                    "chain": "' . $chain . '",
                    "tx-hash": "",
                    "amount": ' . $amount . ',
                    "from-addr-tag": "",
                    "address": "1PL24EbWrNNrnMKw1cxAHPsebUz7DdhWTx",
                    "address-tag": "",
                    "fee": 0E-18,
                    "state": "confirmed",
                    "created-at": 1637758163686,
                    "updated-at": 1637758251559
                }
            }
            ');
    }


    private function mockGetMarketDetailMethodResponse(array $params = ["symbol" => "btc"])
    {
        $symbol = $params['symbol'];

        return match ($symbol) {
            CurrencySymbol::BTC->toLower() => json_decode('{"ch":"market.btcusdt.detail","status":"ok","ts":1707306152971,"tick":{"id":336983710016,"low":42648.01,"high":43403.57,"open":42787.34,"close":42906.74,"vol":4.794801314533072E7,"amount":1114.2828653751951,"version":336983710016,"count":62112}}'),
            CurrencySymbol::ETH->toLower() => json_decode('{"ch":"market.ethusdt.detail","status":"ok","ts":1707310816451,"tick":{"id":316516040040,"low":2319.33,"high":2391.65,"open":2327.27,"close":2372.09,"vol":2.1911934938364983E7,"amount":9267.808645534136,"version":316516040040,"count":22456}}'),
        };
    }

    private function itPlacesWithdrawalRequestsDataProvider(): array
    {
        return [
            'usdt' => [
                'balance'                            => '100.123456789',
                'chain'                              => ExchangeChainType::TRC20USDT,
                'currency'                           => CurrencySymbol::USDT->toLower(),
                'exchangeWithdrawalWalletMinBalance' => 0,
                'withdrawalAmount'                   => 50.1234,
            ],
            'btc'  => [
                'balance'                            => '1',
                'chain'                              => ExchangeChainType::BTC,
                'currency'                           => CurrencySymbol::BTC->toLower(),
                'exchangeWithdrawalWalletMinBalance' => 0,
                'withdrawalAmount'                   => 0.99880000,
            ],
            'eth'  => [
                'balance'                            => '0.5',
                'chain'                              => ExchangeChainType::ETH,
                'currency'                           => CurrencySymbol::ETH->toLower(),
                'exchangeWithdrawalWalletMinBalance' => 0,
                'withdrawalAmount'                   => 0.47850000,
            ],
        ];
    }

    private function itDoesNotPlacesWithdrawalRequestsToHuobiOverMinimalBalanceDataProvider(): array
    {
        return [
            'usdt' => [
                'balance'                            => '100.123456789',
                'chain'                              => ExchangeChainType::TRC20USDT,
                'currency'                           => CurrencySymbol::USDT->toLower(),
                'exchangeWithdrawalWalletMinBalance' => 100.13,
            ],
            'btc'  => [
                'balance'                            => '1',
                'chain'                              => ExchangeChainType::BTC,
                'currency'                           => CurrencySymbol::BTC->toLower(),
                'exchangeWithdrawalWalletMinBalance' => 1.01,
            ],
            'eth'  => [
                'balance'                            => '0.5',
                'chain'                              => ExchangeChainType::ETH,
                'currency'                           => CurrencySymbol::ETH->toLower(),
                'exchangeWithdrawalWalletMinBalance' => 0.51,
            ],
        ];
    }
}