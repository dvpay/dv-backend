<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\WebhookStatus;
use App\Models\Invoice;
use App\Models\Webhook;
use App\Models\WebhookSendHistory;
use Illuminate\Database\Eloquent\Collection;

class WebhookRepository
{
    public function getByInvoiceId($invoiceId): ?Collection
    {
        return Webhook::where('invoice_id', $invoiceId)->get();
    }

    public function checkWebhookIsHandled(Invoice $invoice, Webhook $webhook): bool
    {
        return WebhookSendHistory::where([
            ['invoice_id', $invoice->id],
            ['type', $invoice->status->event()],
            ['status', WebhookStatus::Success],
            ['url', $webhook->url],
        ])->exists();
    }
}