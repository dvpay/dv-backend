<?php

namespace Tests\Feature\Payer;

 use App\Enums\CurrencyId;
 use App\Models\Payer;
 use App\Models\PayerAddress;
 use App\Models\Store;
 use App\Models\UnconfirmedTransaction;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Testing\Fluent\AssertableJson;
 use Symfony\Component\HttpFoundation\Response;
 use Tests\TestCase;

class GetPayerTest extends TestCase
{
    use RefreshDatabase;

    public function testItDoesNotDisclosesPrivateDataWithStaticGenerationEnabled()
    {

        $payer = Payer::factory()->create();

        $payer->store()->update([
            'static_addresses' => true,
            'status' => true,
        ]);

        $btcBitcoinAddress = PayerAddress::factory()
            ->for($payer)
            ->create([
                'currency_id' => CurrencyId::BtcBitcoin->value,
                'blockchain' => CurrencyId::BtcBitcoin->getBlockchain(),
            ]);

        $response = $this->get('/payer/' . $payer->id);

        $response->assertStatus(Response::HTTP_OK);

        $result = json_decode($response->getContent(),true);


        $expected = [
            'result' =>
                [
                    'id' => $payer->id,
                    'store_id' => $payer->store->id,
                    'store' =>
                        [
                            'id' => $payer->store->id,
                            'name' => $payer->store->name,
                            'status' => 1,
                            'staticAddress' => 1,
                            'storeCurrencyCode' => 'USD',
                            'returnUrl' => NULL,
                            'successUrl' => NULL,
                            'siteUrl' => $payer->store->site,
                        ],
                    'address' =>
                        [
                            0 =>
                                [
                                    'blockchain' => 'bitcoin',
                                    'currency' => 'BTC.Bitcoin',
                                    'address' => $btcBitcoinAddress->address,
                                    'rate' => null,
                                    'payer' =>
                                        [
                                            'id' => $payer->id,
                                            'store' => $payer->store->id,
                                            'storeName' => $payer->store->name,
                                            'payerUrl' => '/payer/' . $payer->id,
                                        ],
                                    'transactions' => [],
                                    'unconfirmedTransactions' => [],
                                ],
                        ],
                    'currency' =>
                        [
                            0 => 'BTC.Bitcoin',
                            1 => 'USDT.Tron',
                        ],
                    'rate' =>
                        [
                            'BTC.Bitcoin' => '0.00001933',
                            'USDT.Tron' => '1',
                        ],
                    'payerUrl' => '/payer/' . $payer->id,
                ],
            'errors' => [],
        ];

        $this->assertEquals($expected,$result);

    }

    public function testItDoesNotDisclosesPrivateDataWithStaticGenerationDisabled()
    {

        $payer = Payer::factory()->create();

        $payer->store()->update([
            'static_addresses' => false,
            'status' => true,
        ]);

        $btcBitcoinAddress = PayerAddress::factory()
            ->for($payer)
            ->create([
                'currency_id' => CurrencyId::BtcBitcoin->value,
                'blockchain' => CurrencyId::BtcBitcoin->getBlockchain(),
            ]);

        $response = $this->get('/payer/' . $payer->id);

        $response->assertStatus(Response::HTTP_OK);

        $result = json_decode($response->getContent(),true);


        $expected = [
            'result' =>
                [
                    'id' => $payer->id,
                    'store_id' => $payer->store->id,
                    'store' =>
                        [
                            'id' => $payer->store->id,
                            'name' => $payer->store->name,
                            'status' => 1,
                            'staticAddress' => 0,
                            'storeCurrencyCode' => 'USD',
                            'returnUrl' => NULL,
                            'successUrl' => NULL,
                            'siteUrl' => $payer->store->site,
                        ],
                    'address' =>
                        [
                            0 =>
                                [
                                    'blockchain' => 'bitcoin',
                                    'currency' => 'BTC.Bitcoin',
                                    'address' => 'Static address generation is disabled in store settings',
                                    'rate' => null,
                                    'payer' =>
                                        [
                                            'id' => $payer->id,
                                            'store' => $payer->store->id,
                                            'storeName' => $payer->store->name,
                                            'payerUrl' => '/payer/' . $payer->id,
                                        ],
                                    'transactions' => [],
                                    'unconfirmedTransactions' => [],
                                ],
                        ],
                    'currency' =>
                        [
                            0 => 'BTC.Bitcoin',
                            1 => 'USDT.Tron',
                        ],
                    'rate' =>
                        [
                            'BTC.Bitcoin' => '0.00001933',
                            'USDT.Tron' => '1',
                        ],
                    'payerUrl' => '/payer/' . $payer->id,
                ],
            'errors' => [],
        ];

        $this->assertEquals($expected,$result);


    }

    public function testItDoesNotDisclosesPrivateDataWithStoreInactive()
    {

        $payer = Payer::factory()->create();

        $payer->store()->update([
            'static_addresses' => true,
            'status' => false,
        ]);

        $btcBitcoinAddress = PayerAddress::factory()
            ->for($payer)
            ->create([
                'currency_id' => CurrencyId::BtcBitcoin->value,
                'blockchain' => CurrencyId::BtcBitcoin->getBlockchain(),
            ]);

        $response = $this->get('/payer/' . $payer->id);

        $response->assertStatus(Response::HTTP_OK);

        $result = json_decode($response->getContent(),true);


        $expected = [
            'result' =>
                [
                    'id' => $payer->id,
                    'store_id' => $payer->store->id,
                    'store' =>
                        [
                            'id' => $payer->store->id,
                            'name' => $payer->store->name,
                            'status' => 0,
                            'staticAddress' => 1,
                            'storeCurrencyCode' => 'USD',
                            'returnUrl' => NULL,
                            'successUrl' => NULL,
                            'siteUrl' => $payer->store->site,
                        ],
                    'address' =>
                        [
                            0 =>
                                [
                                    'blockchain' => 'bitcoin',
                                    'currency' => 'BTC.Bitcoin',
                                    'address' => 'The store has suspended accepting payments, contact the owner to enable it',
                                    'rate' => null,
                                    'payer' =>
                                        [
                                            'id' => $payer->id,
                                            'store' => $payer->store->id,
                                            'storeName' => $payer->store->name,
                                            'payerUrl' => '/payer/' . $payer->id,
                                        ],
                                    'transactions' => [],
                                    'unconfirmedTransactions' => [],
                                ],
                        ],
                    'currency' =>
                        [
                            0 => 'BTC.Bitcoin',
                            1 => 'USDT.Tron',
                        ],
                    'rate' =>
                        [
                            'BTC.Bitcoin' => '0.00001933',
                            'USDT.Tron' => '1',
                        ],
                    'payerUrl' => '/payer/' . $payer->id,
                ],
            'errors' => [],
        ];

        $this->assertEquals($expected,$result);


    }

    public function testItDoesNotDisclosesPrivateDataWhenCreatesNewAddress()
    {

        $payer = Payer::factory()
            ->for(
                Store::factory([
                    'static_addresses' => true,
                ])
            )
            ->create();

        $responseAddress = $this->get('/payer/' . $payer->id . '/addresses/' . CurrencyId::UsdtTron->value);

        $responseAddress->assertStatus(Response::HTTP_CREATED);

        $responseAddress->assertJson(fn (AssertableJson $json) =>
            $json->has('result', 7)
                ->has('result.blockchain')
                ->has('result.currency')
                ->has('result.address')
                ->has('result.rate')
                ->has('result.payer',4)
                ->has('result.payer.id')
                ->has('result.payer.store')
                ->has('result.payer.storeName')
                ->has('result.payer.payerUrl')
                ->has('result.transactions',0)
                ->has('result.unconfirmedTransactions',0)
                ->has('errors',0)

        );

    }

    public function testItReturnsUnconfirmedTransactions()
    {

        $payer = Payer::factory()->create();

        $usdtTronAddress = PayerAddress::factory()
            ->for($payer)
            ->create([
                'currency_id' => CurrencyId::UsdtTron->value,
                'blockchain' => CurrencyId::UsdtTron->getBlockchain(),
            ]);

        $btcBitcoinAddress = PayerAddress::factory()
            ->for($payer)
            ->create([
                'currency_id' => CurrencyId::BtcBitcoin->value,
                'blockchain' => CurrencyId::BtcBitcoin->getBlockchain(),
            ]);


        UnconfirmedTransaction::factory()->create([
            'to_address' => $usdtTronAddress->address,
            'currency_id' => $usdtTronAddress->currency_id,
        ]);

        UnconfirmedTransaction::factory()->create([
            'to_address' => $btcBitcoinAddress->address,
            'currency_id' => $btcBitcoinAddress->currency_id,
        ]);


        $response = $this->get('/payer/' . $payer->id);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('result.address', 2)
                ->has('result.address.0', fn (AssertableJson $json) =>
                $json->has('unconfirmedTransactions', 1)
                    ->etc()
                )
                ->has('result.address.1', fn (AssertableJson $json) =>
                $json->has('unconfirmedTransactions', 1)
                    ->etc()
                )
                ->etc()
        );

        $result = json_decode($response->getContent(),true);

        $addressCollection = collect($result['result']['address']);

        $btcAddresses = $addressCollection->filter(function ($item) {
            return $item['currency'] === CurrencyId::BtcBitcoin->value;
        });

        $this->assertEquals(
            $btcBitcoinAddress->address,
            $btcAddresses->first()['unconfirmedTransactions'][0]['to_address']
        );

        $usdtAddresses = $addressCollection->filter(function ($item) {
            return $item['currency'] === CurrencyId::UsdtTron->value;
        });

        $this->assertEquals(
            $usdtTronAddress->address,
            $usdtAddresses->first()['unconfirmedTransactions'][0]['to_address']
        );

    }

}
