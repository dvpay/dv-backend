<?php

namespace Tests\Unit\Processing;

 use App\Dto\Transfer\TransferDto;
 use App\Enums\CurrencyId;
 use App\Enums\HttpMethod;
 use App\Enums\TransferKind;
 use App\Enums\TransferStatus;
 use App\Models\Currency;
 use App\Models\User;
 use App\Services\Processing\Contracts\Client;
 use App\Services\Processing\ProcessingService;
 use GuzzleHttp\Psr7\Response;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Support\Str;
 use Tests\TestCase;

class ProcessingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testItCanTransferFromAddress()
    {
        $currencyId = CurrencyId::UsdtTron;
        $addressFrom = Str::random(40);
        $addressTo = Str::random(40);
        $amount = 0.01;
        $amountUsd = 0.01;

        $user = User::factory()->create();

        $currency = Currency::find($currencyId);

        $processingWalletService = \Mockery::mock(Client::class)->makePartial();
        $processingWalletService->shouldReceive('request')
            ->withArgs([
                HttpMethod::POST,
                "owners/{$user->processing_owner_id}/transfer",
                \Mockery::subset([
                    'wallet'     => $addressFrom,
                    'address'    => $addressTo,
                    'blockchain' => $currency->blockchain->value,
                    'owner'      => $user->processing_owner_id,
                    'isManual'   => false,
                    'contract'   => $currency->contract_address,
                ]),
                'owners/{ownerId}/transfer'
            ])
            ->andReturn(new Response(200));

        $this->app->instance(Client::class, $processingWalletService);

        $processingService = app(ProcessingService::class);

        $dto = new TransferDto(
            [
                'uuid'        => Str::uuid(),
                'user'        => $user,
                'kind'        => TransferKind::TransferFromProcessing,
                'currency'    => $currency,
                'status'      => TransferStatus::Waiting,
                'addressFrom' => $addressFrom,
                'addressTo'   => $addressTo,
                'contract'    => $currency->contract_address,
                'amount'      => $amount,
                'amountUsd'   => $amountUsd,
            ]
        );

        $result = $processingService->transferFromAddress($dto);

        $this->assertTrue($result);

    }

    public function testItCanTransferFromProcessing()
    {
        $currencyId = CurrencyId::UsdtTron;
        $addressTo = Str::random(40);
        $amount = 1.51;
        $uuid = Str::uuid();

        $user = User::factory()->create();

        $currency = Currency::find($currencyId);

        $processingWalletService = \Mockery::mock(Client::class)->makePartial();
        $processingWalletService->shouldReceive('request')
            ->withArgs([
                HttpMethod::POST,
                "owners/{$user->processing_owner_id}/tron/withdrawal",
                \Mockery::type('array'),
                'owners/{ownerId}/tron/withdrawal'
            ])
            ->andReturn(new Response(200));

        $this->app->instance(Client::class, $processingWalletService);

        $processingService = app(ProcessingService::class);

        $dto = new TransferDto(
            [
                'uuid'        => $uuid,
                'user'        => $user,
                'kind'        => TransferKind::TransferFromProcessing,
                'currency'    => $currency,
                'status'      => TransferStatus::Waiting,
                'addressTo'   => $addressTo,
                'contract'    => $currency->contract_address,
                'amount'      => $amount,
            ]
        );

        $result = $processingService->transferFromProcessing($dto);

        $this->assertTrue($result);

    }

 }
