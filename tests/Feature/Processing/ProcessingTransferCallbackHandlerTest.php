<?php

namespace Tests\Feature\Processing;

 use App\Enums\Blockchain;
 use App\Enums\CurrencyId;
 use App\Enums\CurrencySymbol;
 use App\Enums\ProcessingCallbackType;
 use App\Enums\UserRole;
 use App\Models\Currency;
 use App\Models\Invoice;
 use App\Models\InvoiceAddress;
 use App\Models\Payer;
 use App\Models\PayerAddress;
 use App\Models\Store;
 use App\Models\StoreApiKey;
 use App\Models\User;
 use App\Models\Wallet;
 use App\Models\WalletBalance;
 use Database\Seeders\RoleSeeder;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Testing\Fluent\AssertableJson;
 use Symfony\Component\HttpFoundation\Response;
 use Tests\TestCase;

class ProcessingTransferCallbackHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function testHandledStatusOk()
    {
        /**
         * Prepare
         */
        $user = User::factory()->create();
        Store::factory()->create([
            'user_id' => $user->id,
            'processing_owner_id' => 'asdasdasdASD',
        ]);

        $payer = Payer::factory()->create();
        $currencyId = CurrencyId::UsdtTron;

        $usdtTronAddress = PayerAddress::factory()
            ->for($payer)
            ->create([
                'currency_id' => $currencyId->value,
                'blockchain' => $currencyId->getBlockchain(),
            ]);

        $currency = Currency::where('id', $currencyId->value)->first();

        $txId = 'ASdaSdASdAsdASdASdAsd';

        $body = [
            'id' => $user->processing_owner_id,
            'tx' => $txId,
            'amount' => '100',
            'blockchain' => $usdtTronAddress->blockchain->value,
            'address' => '13213213213213213',
            'sender' => $usdtTronAddress->address,
            'confirmations' => "6",
            'contractAddress' => $currency->contract_address,
            'time' => '2022-01-01 20:42:10',
            'type' => ProcessingCallbackType::Transfer->value,
            'isManual' => "1",
            'ownerId' => $user->processing_owner_id,
        ];
        $jsonBody = json_encode($body);
        $sign = hash('sha256', $jsonBody . config('processing.client.webhookKey'));

        /**
         * Send
         */

        $response = $this->post('/processing/callback',
            $body
            ,[
                'X-Sign' => $sign,
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_ACCEPTED);

        $this->assertDatabaseHas('transactions', [
                'user_id' => $user->id,
                'tx_id' => $txId,
                'from_address' => $usdtTronAddress->address,
                'to_address' => '13213213213213213',
            ]);
    }

    public function testHandledNegativeAmountStatusBadRequest()
    {
        /**
         * Prepare
         */
        $user = User::factory()->create();
        Store::factory()->create([
            'user_id' => $user->id,
            'processing_owner_id' => 'asdasdasdASD',
        ]);

        $payer = Payer::factory()->create();
        $currencyId = CurrencyId::UsdtTron;

        $usdtTronAddress = PayerAddress::factory()
            ->for($payer)
            ->create([
                'currency_id' => $currencyId->value,
                'blockchain' => $currencyId->getBlockchain(),
            ]);

        $currency = Currency::where('id', $currencyId->value)->first();

        $txId = 'ASdaSdASdAsdASdASdAsd';
        $receiver = 'zxcZXczxcZxcZxcZxczxcZXCZxc';
        $body = [
            'id' => $user->processing_owner_id,
            'tx' => $txId,
            'amount' => '-100',
            'blockchain' => $usdtTronAddress->blockchain->value,
            'address' => $receiver,
            'sender' => $usdtTronAddress->address,
            'contractAddress' => $currency->contract_address,
            'time' => '2022-01-01 20:42:10',
            'type' => ProcessingCallbackType::Transfer->value,
            'isManual' => "1",
            'ownerId' => $user->processing_owner_id,
        ];
        $jsonBody = json_encode($body);
        $sign = hash('sha256', $jsonBody . config('processing.client.webhookKey'));

        /**
         * Send
         */

        $response = $this->post('/processing/callback',
            $body
            ,[
                'X-Sign' => $sign,
                'Accept' => 'application/json',
            ]
        );

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertDatabaseCount('transactions',0);
    }

}
