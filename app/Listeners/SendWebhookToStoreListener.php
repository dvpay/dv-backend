<?php

namespace App\Listeners;

use App\Enums\Queue;
use App\Enums\WebhookType;
use App\Events\PaymentReceivedEvent;
use App\Events\UnconfirmedTransactionCreatedEvent;
use App\Repositories\WebhookRepository;
use App\Services\Webhook\WebhookDataService;
use App\Services\Webhook\WebhookManager;
use App\WebhookServer\Exceptions\CouldNotCallWebhookException;
use App\WebhookServer\Exceptions\InvalidSignerException;
use App\WebhookServer\Exceptions\InvalidWebhookJobException;
use App\WebhookServer\WebhookCall;
use InvalidArgumentException;

readonly class SendWebhookToStoreListener
{
    public function __construct(
        private WebhookManager     $webhookManager,
        private WebhookDataService $webhookDataService,
        private WebhookRepository  $webhookRepository
    )
    {
    }

    /**
     * @throws InvalidWebhookJobException
     * @throws CouldNotCallWebhookException
     * @throws InvalidSignerException
     */
    public function handle(PaymentReceivedEvent|UnconfirmedTransactionCreatedEvent $event): void
    {
        if ($event instanceof PaymentReceivedEvent) {
            $type = WebhookType::PaymentReceived;
            $transaction = $event->transaction;
            $requestData = $this->webhookDataService->getTransactionData($transaction);
        } else if ($event instanceof UnconfirmedTransactionCreatedEvent) {
            $type = WebhookType::UnconfirmedTransaction;
            $transaction = $event->unconfirmedTransaction;
            $requestData = $this->webhookDataService->getUnconfirmedWebhookData($transaction);
        } else {
            throw new InvalidArgumentException('Unsupported event type');
        }

        if ($transaction->store->minimal_payment >= $transaction->amount_usd) {
            return;
        }

        if (!$webhooks = $this->webhookManager->getWebhooksByStore($transaction->store_id, $type)) {
            return;
        }


        foreach ($webhooks as $webhook) {
            if ($this->webhookRepository->checkWebhookIsSend($webhook, $type, $transaction)) {
                continue;
            }

            $queue = $event->attempts === 1 ? Queue::StoreWebHook : Queue::StoreWebHookRetry;

            WebhookCall::create()
                ->url($webhook->url)
                ->payload($requestData)
                ->useSecret($webhook->secret)
                ->meta([
                    'eventType'   => $type->name,
                    'transaction' => $transaction
                ])
                ->delay($event->delay)
                ->attempts($event->attempts)
                ->onQueue($queue->value)
                ->dispatch();
        }
    }
}
