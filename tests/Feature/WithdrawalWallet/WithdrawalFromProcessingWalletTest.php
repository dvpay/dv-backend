<?php

namespace Tests\Feature\WithdrawalWallet;

 use App\Dto\ProcessingWalletDto;
 use App\Enums\Blockchain;
 use App\Enums\CurrencyId;
 use App\Enums\RateSource;
 use App\Enums\TransferKind;
 use App\Enums\TransferStatus;
 use App\Enums\TransferType;
 use App\Enums\UserRole;
 use App\Models\Currency;
 use App\Models\Store;
 use App\Models\User;
 use App\Models\Wallet;
 use App\Models\WalletBalance;
 use App\Services\Processing\Contracts\ProcessingWalletContract;
 use App\Services\Processing\Contracts\TransferContract;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Support\Str;
 use Symfony\Component\HttpFoundation\Response;
 use Tests\TestCase;

class WithdrawalFromProcessingWalletTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminCanWithdrawFromProcessingWallet()
    {

        $currencyId = CurrencyId::UsdtTron;
        $processingAddress = Str::random(40);
        $processingBalance = "10.1";
        $addressTo = 'T' . Str::replace('0', '9', Str::random(33));

        $amount = 1;
        $amountUsd = 1;


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

        $this->actingAs($user, 'sanctum');


        $processingWalletDto = new ProcessingWalletDto([
            "address"        => $processingAddress,
            "balance"        => $processingBalance,
            "blockchain"     => $currencyId->getBlockchain(),
            'bandwidth'      => rand(1000, 9999999),
            'bandwidthLimit' => rand(1000, 9999999),
            'energy'         => rand(1000, 9999999),
            'energyLimit'    => rand(1000, 9999999),
            'transferType'   => TransferType::Delegate->value,
        ]);

        $processingWalletService = \Mockery::mock(ProcessingWalletContract::class)->makePartial();
        $processingWalletService->shouldReceive('getWallets')
            ->with($user->processing_owner_id)
            ->andReturn([$processingWalletDto]);

        $this->app->instance(ProcessingWalletContract::class, $processingWalletService);


        $processingService = \Mockery::mock(TransferContract::class)->makePartial();
        $processingService->shouldReceive('transferFromProcessing')
            ->andReturn(true);

        $this->app->instance(TransferContract::class, $processingService);


        $response = $this->post('withdrawal-wallet/withdrawal-from-processing-wallet',
            [
                'currencyId' => $currencyId->value,
                'addressTo' => $addressTo,
                'amount' => $amount,
            ],
            [
                'Accept' => 'application/json',
            ]);

        $response->assertStatus(Response::HTTP_OK);

        $this->assertDatabaseHas('transfers',[
            'user_id'      => $user->id,
            'kind'         => TransferKind::TransferFromProcessing,
            'currency_id'  => $currencyId,
            'status'       => TransferStatus::Waiting,
            'address_from' => $processingWalletDto->address,
            'address_to'   => $addressTo,
            'amount'       => $amount,
            'amount_usd'   => $amountUsd,
        ]);
    }

    public function testitValidatesTronAddress()
    {

        $currencyId = CurrencyId::UsdtTron;
        $processingAddress = Str::random(40);
        $processingBalance = "10.1";
        $addressTo = Str::random(40);
        $amount = 0.01;


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

        $this->actingAs($user, 'sanctum');


        $processingWalletDto = new ProcessingWalletDto([
            "address"        => $processingAddress,
            "balance"        => $processingBalance,
            "blockchain"     => $currencyId->getBlockchain(),
            'bandwidth'      => rand(1000, 9999999),
            'bandwidthLimit' => rand(1000, 9999999),
            'energy'         => rand(1000, 9999999),
            'energyLimit'    => rand(1000, 9999999),
            'transferType'   => TransferType::Delegate->value,
        ]);

        $processingWalletService = \Mockery::mock(ProcessingWalletContract::class)->makePartial();
        $processingWalletService->shouldReceive('getWallets')
            ->with($user->processing_owner_id)
            ->andReturn([$processingWalletDto]);

        $this->app->instance(ProcessingWalletContract::class, $processingWalletService);


        $processingService = \Mockery::mock(TransferContract::class)->makePartial();
        $processingService->shouldReceive('transferFromProcessing')
            ->andReturn(true);

        $this->app->instance(TransferContract::class, $processingService);


        $response = $this->post('withdrawal-wallet/withdrawal-from-processing-wallet',
            [
                'currencyId' => $currencyId->value,
                'addressTo' => $addressTo,
                'amount' => $amount,
            ],
            [
                'Accept' => 'application/json',
            ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

    }

}
