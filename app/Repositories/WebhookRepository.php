<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\WebhookStatus;
use App\Enums\WebhookType;
use App\Models\Invoice;
use App\Models\Model;
use App\Models\Transaction;
use App\Models\UnconfirmedTransaction;
use App\Models\Webhook;
use App\Models\WebhookSendHistory;
use Illuminate\Database\Eloquent\Collection;
use JetBrains\PhpStorm\Deprecated;

class WebhookRepository
{
    public function getByInvoiceId($invoiceId): ?Collection
    {
        return Webhook::where('invoice_id', $invoiceId)->get();
    }

    #[Deprecated]
    public function checkWebhookIsHandled(Invoice $invoice, Webhook $webhook): bool
    {
        return WebhookSendHistory::where([
            ['invoice_id', $invoice->id],
            ['type', $invoice->status->event()],
            ['status', WebhookStatus::Success],
            ['url', $webhook->url],
        ])->exists();
    }

    public function checkWebhookIsSend(Webhook $webhook, WebhookType $type, Transaction|UnconfirmedTransaction $transaction): bool
    {
        return WebhookSendHistory::where([
            'status'  => WebhookStatus::Success,
            'type'    => $type->name,
            'url'     => $webhook->url,
            'tx_hash' => $transaction->tx_id
        ])->exists();
    }
}