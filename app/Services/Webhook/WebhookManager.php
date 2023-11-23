<?php

declare(strict_types=1);

namespace App\Services\Webhook;

use App\Enums\InvoiceStatus;
use App\Enums\WebhookType;
use App\Models\Invoice;
use App\Models\Webhook;
use App\Repositories\WebhookRepository;
use Illuminate\Support\Facades\Log;

class WebhookManager
{
    public function __construct(private readonly WebhookRepository $webhookRepository)
    {
    }

    /**
     * @param  Invoice  $invoice
     * @param  bool  $skipCheckHandledWebhook
     * @param  bool  $sendSuccessWebhook
     *
     * @return Webhook[]
     */
    public function getWebhooks(Invoice $invoice, bool $skipCheckHandledWebhook, bool $sendSuccessWebhook): array
    {
        $result = [];

        if ($invoice->status == InvoiceStatus::Success && !$sendSuccessWebhook) {
            Log::channel('supportLog')->error('Invoice '.$invoice->id.' no webhook success status');
            return $result;
        }

        if (!$event = $invoice->status->event()) {
            Log::channel('supportLog')->error('Invoice '.$invoice->id.' no webhook event');
            return $result;
        }

        foreach ($invoice->webhooks as $webhook) {
            if ($this->checkHandledWebhook($invoice, $webhook, $skipCheckHandledWebhook)) {
                continue;
            }

            if (in_array($event->value, $webhook->events)) {
                $result[] = $webhook;
            }
        }
        return $result;
    }

    public function checkHandledWebhook(Invoice $invoice, Webhook $webhook, bool $skipCheckHandledWebhook): bool
    {

        if ($skipCheckHandledWebhook || $invoice->status->event() === WebhookType::PaymentReceived) {
            return false;
        }

        return $this->webhookRepository->checkWebhookIsHandled($invoice, $webhook);
    }
}