<?php

namespace Feature\HotWallets;

 use App\Enums\Blockchain;
 use App\Enums\CurrencyId;
 use App\Enums\RateSource;
 use App\Enums\UserRole;
 use App\Models\HotWallet;
 use App\Models\User;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Tests\TestCase;

class GetHotWalletsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @dataProvider hotWalletsDataProvider
     */
    public function testHotWalletsWithCorrectUsdAmount(
        $currencyId,
        $amount,
        $amountUSD,
        $expectedAmountUSD,
    )
    {

        $address = 'Privet_Drive';

        $user = User::factory()->create([
            'rate_source' => RateSource::LoadRateFake->value,
        ]);
        $user->assignRole([UserRole::Admin->value]);

        $this->actingAs($user, 'sanctum');

        $hotWallet = HotWallet::factory()->create([
            'user_id' => $user,
            'currency_id' => $currencyId,
            'address' => $address,
            'blockchain' => $currencyId->getBlockchain(),
            'amount' => $amount,
            'amount_usd' => $amountUSD,
        ]);



        $response = $this->get("/hot-wallets");

        $result = json_decode($response->getContent(),true);

        var_export(json_decode($response->getContent(),true));

        $expected = [
            'result' => [
                [
                    'id' => $hotWallet->id,
                    'currency' => $currencyId->value,
                    'currencyId' => $currencyId->value,
                    'address' => $address,
                    'blockchain' => $currencyId->getBlockchain(),
                    'createdAt' => $hotWallet->created_at->format('Y-m-d\TH:i:s') . '.000000Z',
                    'updatedTt' => $hotWallet->updated_at->format('Y-m-d\TH:i:s') . '.000000Z',
                    'balance' => $amount,
                    'balanceUsd' => $expectedAmountUSD,
                    'explorerUrl' => Blockchain::from($currencyId->getBlockchain())->getExplorerUrlAddress() . '/' . $address,
                ]
            ],
            'links' =>
                [
                    'first' => 'https://dv.net/hot-wallets?page=1',
                    'last' => 'https://dv.net/hot-wallets?page=1',
                    'prev' => NULL,
                    'next' => NULL,
                ],
            'meta' =>
                [
                    'current_page' => 1,
                    'from' => 1,
                    'last_page' => 1,
                    'links' =>
                        [
                            [
                                'url' => NULL,
                                'label' => '&laquo; Previous',
                                'active' => false,
                            ],
                            [
                                'url' => 'https://dv.net/hot-wallets?page=1',
                                'label' => '1',
                                'active' => true,
                            ],
                            [
                                'url' => NULL,
                                'label' => 'Next &raquo;',
                                'active' => false,
                            ],
                        ],
                    'path' => 'https://dv.net/hot-wallets',
                    'per_page' => 10,
                    'to' => 1,
                    'total' => 1,
                ],
            'errors' =>
                [],
        ];

        $this->assertEquals($expected,$result);

    }

    private function hotWalletsDataProvider(): array
    {
        return [
            'btc' => [
                'currencyId' => CurrencyId::BtcBitcoin,
                'amount' => '666.00000000',
                'amountUSD' => '777.00000000',
                'expectedAmountUSD' => '0.01',
            ],
            'usdt' => [
                'currencyId' => CurrencyId::UsdtTron,
                'amount' => '666.00000000',
                'amountUSD' => '23423.00000000',
                'expectedAmountUSD' => '666',
            ],
            'eth' => [
                'currencyId' => CurrencyId::EthEthereum,
                'amount' => '666.00000000',
                'amountUSD' => '111.00000000',
                'expectedAmountUSD' => '0.23',
            ],
        ];
    }

}
