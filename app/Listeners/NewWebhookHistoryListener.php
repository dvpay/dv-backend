<?php

namespace App\Listeners;

use App\Enums\WebhookStatus;
use App\Models\WebhookSendHistory;
use App\WebhookServer\Events\WebhookCallFailedEvent;
use App\WebhookServer\Events\WebhookCallSucceededEvent;
use Illuminate\Support\Str;

class NewWebhookHistoryListener
{
    public function handle(WebhookCallSucceededEvent|WebhookCallFailedEvent $event): void
    {

        $responseCode = $event->response->getStatusCode();

        if (Str::startsWith($event->response->getStatusCode(), 2)) {
            $webhookStatus = WebhookStatus::Success;
        } else {
            $webhookStatus = WebhookStatus::Fail;
        }

        $transaction = $event->meta['transaction'];

        WebhookSendHistory::create([
            'invoice_id'           => $transaction->invoice_id ?? '',
            'type'                 => $event->meta['eventType'],
            'url'                  => $event->webhookUrl,
            'status'               => $webhookStatus,
            'request'              => json_encode($event->payload),
            'response'             => $event->response->getBody()->getContents(),
            'response_status_code' => $responseCode,
            'tx_hash'              => $transaction->tx_id
        ]);
    }
}
