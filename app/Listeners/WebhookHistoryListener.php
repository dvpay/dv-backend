<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\WebhookStatus;
use App\Events\WebhookIsSentEvent;
use App\Models\InvoiceHistory;
use App\Models\WebhookSendHistory;
use Symfony\Component\HttpFoundation\Response;

/**
 * WebhookHistoryListener
 */
class WebhookHistoryListener
{
    /**
     * @param WebhookIsSentEvent $event
     * @return void
     */
    public function handle(WebhookIsSentEvent $event): void
    {
        $invoice = $event->invoice;
        $webhook = $event->webhook;
        $request = $event->request;
        $response = $event->response;
        $responseCode = $event->responseCode;

        if ($responseCode == Response::HTTP_OK) {
            $webhookStatus = WebhookStatus::Success;
        } else {
            $webhookStatus = WebhookStatus::Fail;
        }

        $history = new WebhookSendHistory([
            'invoice_id'           => $invoice->id,
            'type'                 => $invoice->status->event(),
            'url'                  => $webhook ? $webhook->url : '',
            'status'               => $webhookStatus,
            'request'              => json_encode($request),
            'response'             => $response,
            'response_status_code' => $responseCode,
        ]);

        $history->save();

        InvoiceHistory::create([
            'invoice_id'     => $invoice->id,
            'text'           => 'Webhook is sent to store :storeName  with status: :webhookValue',
            'text_variables' => [
                'storeName'    => $invoice->store->name,
                'webhookValue' => $webhookStatus->value
            ]
        ]);

    }
}
