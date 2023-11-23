<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\WebhookStatus;
use App\Models\Invoice;
use App\Models\Webhook;
use App\Models\WebhookSendHistory;
use Illuminate\Database\Eloquent\Collection;

class WebhookSendHistoryRepository
{
    public function getByInvoiceId($invoiceId): ?Collection
    {
        return WebhookSendHistory::where('invoice_id', $invoiceId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}