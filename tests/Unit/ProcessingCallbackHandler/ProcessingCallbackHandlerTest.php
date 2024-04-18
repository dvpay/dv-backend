<?php

namespace Tests\Unit\ProcessingCallbackHandler;

 use App\Dto\ProcessingCallbackDto;
 use App\Enums\Blockchain;
 use App\Enums\CurrencyId;
 use App\Enums\InvoiceStatus;
 use App\Enums\ProcessingCallbackType;
 use App\Events\PaymentReceivedEvent;
 use App\Events\UnconfirmedTransactionCreatedEvent;
 use App\Exceptions\ApiException;
 use App\Models\Payer;
 use App\Models\PayerAddress;
 use App\Services\Processing\ProcessingCallbackHandler;
 use Carbon\Carbon;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Tests\TestCase;

class ProcessingCallbackHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function testItFiresUnconfirmedTransactionCreatedEventOnDepositTransaction()
    {

        $currencyId = CurrencyId::UsdtTron;
        $amount = 31337;
        $txId = sha1(time());
        $fromAddress = md5(time());
        $confirmations = config('processing.min_transaction_confirmations') - 1;

        $payer = Payer::factory()->create();

        $usdtTronAddress = PayerAddress::factory()
            ->for($payer)
            ->create([
                'currency_id' => $currencyId->value,
                'blockchain' => $currencyId->getBlockchain(),
            ]);

        $dto = new ProcessingCallbackDto([
            'blockchain' => Blockchain::from($currencyId->getBlockchain()),
            'payer_id' => $payer->id,
            'address' => $usdtTronAddress->address,
            'type' => ProcessingCallbackType::Deposit,
            'amount' => $amount,
            'tx' => $txId,
            'sender' => $fromAddress,
            'status' => InvoiceStatus::Paid,
            'confirmations' => $confirmations,
        ]);


        \Event::fake([UnconfirmedTransactionCreatedEvent::class]);

        $processingCallbackHandler = app(ProcessingCallbackHandler::class);

        $this->expectException(ApiException::class);

        $processingCallbackHandler->handle($dto);

        $this->assertDatabaseHas('unconfirmed_transactions', [
            'payer_id' => $payer->id,
            'currency_id' => $currencyId->value,
            'tx_id' => $txId,
            'user_id' => $payer->store->user->id,
            'store_id' => $payer->store->id,
            'invoice_id' => null,
            'from_address' => $fromAddress,
            'to_address' => $usdtTronAddress->address,
            'amount' => $amount,
            'amount_usd' => $amount,
        ]);

        \Event::assertDispatched(UnconfirmedTransactionCreatedEvent::class, function ($e) use ($payer , $txId) {
            return $e->unconfirmedTransaction->payer_id === $payer->id && $e->unconfirmedTransaction->tx_id === $txId;
        });

    }

    public function testItFiresPaymentReceivedEventOnDepositTransaction()
    {

        $currencyId = CurrencyId::UsdtTron;
        $amount = 31337;
        $txId = sha1(time());
        $fromAddress = md5(time());
        $confirmations = config('processing.min_transaction_confirmations') + 1;
        $time = Carbon::now();

        $payer = Payer::factory()->create();

        $usdtTronAddress = PayerAddress::factory()
            ->for($payer)
            ->create([
                'currency_id' => $currencyId->value,
                'blockchain' => $currencyId->getBlockchain(),
            ]);

        $dto = new ProcessingCallbackDto([
            'blockchain' => Blockchain::from($currencyId->getBlockchain()),
            'payer_id' => $payer->id,
            'address' => $usdtTronAddress->address,
            'type' => ProcessingCallbackType::Deposit,
            'amount' => $amount,
            'tx' => $txId,
            'sender' => $fromAddress,
            'status' => InvoiceStatus::Paid,
            'confirmations' => $confirmations,
            'time' => $time,
        ]);


        \Event::fake([PaymentReceivedEvent::class]);

        $processingCallbackHandler = app(ProcessingCallbackHandler::class);

        $processingCallbackHandler->handle($dto);

        $this->assertDatabaseHas('transactions', [
            'payer_id' => $payer->id,
            'currency_id' => $currencyId->value,
            'tx_id' => $txId,
            'user_id' => $payer->store->user->id,
            'store_id' => $payer->store->id,
            'from_address' => $fromAddress,
            'to_address' => $usdtTronAddress->address,
            'amount' => $amount,
            'amount_usd' => $amount,
            'network_created_at' => $time
        ]);

        \Event::assertDispatched(PaymentReceivedEvent::class, function ($e) use ($payer , $txId) {
            return $e->transaction->payer_id === $payer->id && $e->transaction->tx_id === $txId;
        });

    }



 }
