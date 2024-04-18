<?php

namespace Tests\Feature\WithdrawalWallet;

 use App\Enums\Blockchain;
 use App\Enums\CurrencyId;
 use App\Enums\CurrencySymbol;
 use App\Enums\ExchangeChainType;
 use App\Enums\RateSource;
 use App\Enums\TransactionType;
 use App\Enums\TransferKind;
 use App\Enums\TransferStatus;
 use App\Enums\UserRole;
 use App\Models\Currency;
 use App\Models\HotWallet;
 use App\Models\Store;
 use App\Models\Transaction;
 use App\Models\User;
 use App\Models\Wallet;
 use App\Models\WalletBalance;
 use App\Models\WithdrawalWallet;
 use App\Models\WithdrawalWalletAddress;
 use App\Services\Processing\Contracts\TransferContract;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Symfony\Component\HttpFoundation\Response;
 use Tests\TestCase;

class WithdrawalFromAddressTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminCanWithdrawFromAddress()
    {

        $user = User::factory()->create();
        $user->assignRole([UserRole::Admin->value]);

        $store = Store::factory()->create([
            'user_id' => $user->id,
            'rate_source' => RateSource::LoadRateFake->value,
        ]);

        $blockchains = Blockchain::cases();
        foreach ($blockchains as $blockchain) {
            $wallet = Wallet::factory()->create([
                'store_id' => $store->id,
                'blockchain' => $blockchain,
            ]);

            $currencies = Currency::where('blockchain', $blockchain->value)->get();
            foreach ($currencies as $currency) {
                WalletBalance::factory()->create([
                    'wallet_id' => $wallet->id,
                    'currency_id' => $currency->id
                ]);
            }
        }

        $withdrawalWallet = WithdrawalWallet::factory()->create([
            'chain' => ExchangeChainType::TRC20USDT,
            'blockchain' => ExchangeChainType::TRC20USDT->blockchain(),
            'currency' => CurrencySymbol::USDT->toLower(),
            'user_id' => $user,
        ]);

        $withdrawalWalletAddress = WithdrawalWalletAddress::factory()->create([
            'withdrawal_wallet_id' => $withdrawalWallet,
        ]);

        $hotWallet = HotWallet::factory()->create();

        $this->actingAs($user, 'sanctum');


        $processingService = \Mockery::mock(TransferContract::class)->makePartial();

        $processingService->shouldReceive('transferFromAddress')
            ->andReturn(true);

        $this->app->instance(TransferContract::class, $processingService);

        $response = $this->post('withdrawal-wallet/withdrawal-from-address',
            [
                'currencyId' => CurrencyId::UsdtTron->value,
                'addressFrom' => $hotWallet->address,
            ],
            [
                'Accept' => 'application/json',
            ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('transfers',[
            'user_id'      => $user->id,
            'kind'         => TransferKind::TransferFromAddress,
            'currency_id'  => CurrencyId::UsdtTron,
            'status'       => TransferStatus::Waiting,
            'address_from' => $hotWallet->address,
        ]);
    }

    public function testItUsesSameWalletToWithdrawFromAddress()
    {

        $user = User::factory()->create();
        $user->assignRole([UserRole::Admin->value]);

        $store = Store::factory()->create([
            'user_id' => $user->id,
            'rate_source' => RateSource::LoadRateFake->value,
        ]);

        $blockchains = Blockchain::cases();
        foreach ($blockchains as $blockchain) {
            $wallet = Wallet::factory()->create([
                'store_id' => $store->id,
                'blockchain' => $blockchain,
            ]);

            $currencies = Currency::where('blockchain', $blockchain->value)->get();
            foreach ($currencies as $currency) {
                WalletBalance::factory()->create([
                    'wallet_id' => $wallet->id,
                    'currency_id' => $currency->id
                ]);
            }
        }

        $withdrawalWallet = WithdrawalWallet::factory()->create([
            'chain' => ExchangeChainType::TRC20USDT,
            'blockchain' => ExchangeChainType::TRC20USDT->blockchain(),
            'currency' => CurrencySymbol::USDT->toLower(),
            'user_id' => $user,
        ]);

        $withdrawalWalletAddress = WithdrawalWalletAddress::factory()->create([
            'withdrawal_wallet_id' => $withdrawalWallet,
        ]);

        #Generate other addresses

        WithdrawalWalletAddress::factory(100)->create([
            'withdrawal_wallet_id' => $withdrawalWallet,
        ]);



        $hotWallet = HotWallet::factory()->create();

        $this->actingAs($user, 'sanctum');


        $processingService = \Mockery::mock(TransferContract::class)->makePartial();

        $processingService->shouldReceive('transferFromAddress')
            ->andReturn(true);

        $this->app->instance(TransferContract::class, $processingService);

        $transaction = Transaction::factory()->create([
            'type' => TransactionType::Transfer,
            'from_address' => $hotWallet->address,
            'to_address'=> $withdrawalWalletAddress->address,
        ]);

        $response = $this->post('withdrawal-wallet/withdrawal-from-address',
            [
                'currencyId' => CurrencyId::UsdtTron->value,
                'addressFrom' => $hotWallet->address,
            ],
            [
                'Accept' => 'application/json',
            ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('transfers',[
            'user_id'      => $user->id,
            'kind'         => TransferKind::TransferFromAddress,
            'currency_id'  => CurrencyId::UsdtTron,
            'status'       => TransferStatus::Waiting,
            'address_from' => $hotWallet->address,
            'address_to'   => $withdrawalWalletAddress->address,
        ]);
    }

}
