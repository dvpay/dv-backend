<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Invoice;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * InvoiceStatusUpdatedEvent
 */
class InvoiceStatusUpdatedEvent
{
    use Dispatchable, InteractsWithQueue, Queueable, InteractsWithSockets, SerializesModels;

    /**
     * @param Invoice $invoice
     * @param bool $skipCheckHandledWebhook
     */
    public function __construct(
        public readonly Invoice $invoice,
        public readonly bool $skipCheckHandledWebhook = false
    )
    {
    }
}