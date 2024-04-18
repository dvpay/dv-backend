<?php

namespace Tests\Unit\SendWebhookToStoreListener;

 use App\Enums\InvoiceStatus;
 use App\Enums\WebhookType;
 use App\Events\PaymentReceivedEvent;
 use App\Events\UnconfirmedTransactionCreatedEvent;
 use App\Models\Transaction;
 use App\Models\UnconfirmedTransaction;
 use App\Models\Webhook;
 use App\WebhookServer\CallWebhookJob;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Support\Facades\Queue;
 use Tests\TestCase;

class SendWebhookToStoreListenerTest extends TestCase
{
    use RefreshDatabase;

    public function testItDispatchesCallWebhookJobOnPaymentReceivedEvent()
    {
        $transaction = Transaction::factory()->create();

        $webhook = Webhook::factory()->create([
            'store_id' => $transaction->store_id,
        ]);

        Queue::fake();

        event(new PaymentReceivedEvent($transaction));

        Queue::assertPushed(CallWebhookJob::class);
        Queue::assertPushed(function (CallWebhookJob $job) use ($transaction) {
            $this->assertEquals(InvoiceStatus::Paid->value,$job->payload['status']);
            $this->assertEquals($transaction->tx_id, $job->payload['transactions'][0]['txId']);
            return true;
        });
    }

    public function testItDispatchesCallWebhookJobOnUnconfirmedTransactionCreatedEvent()
    {
        $unconfirmedTransaction = UnconfirmedTransaction::factory()->create();

        $webhook = Webhook::factory()->create([
            'store_id' => $unconfirmedTransaction->store_id,
        ]);

        Queue::fake();

        event(new UnconfirmedTransactionCreatedEvent($unconfirmedTransaction));

        Queue::assertPushed(CallWebhookJob::class);
        Queue::assertPushed(function (CallWebhookJob $job) use ($unconfirmedTransaction) {
            $this->assertEquals(WebhookType::UnconfirmedTransaction->value,$job->payload['type']);
            $this->assertEquals($unconfirmedTransaction->tx_id, $job->payload['unconfirmed_transaction']['txId']);
            return true;
        });
    }

 }
