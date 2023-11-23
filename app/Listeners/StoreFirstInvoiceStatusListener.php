<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\InvoiceCreatedEvent;
use App\Models\InvoiceHistory;
use App\Models\InvoiceStatusHistory;
use Throwable;

/**
 * StoreFirstInvoiceStatusListener
 */
class StoreFirstInvoiceStatusListener
{
    /**
     * @param InvoiceCreatedEvent $event
     * @return bool
     * @throws Throwable
     */
    public function handle(InvoiceCreatedEvent $event): bool
    {
        $invoice = $event->invoice;

        $invoiceStatusHistory = new InvoiceStatusHistory([
            'invoice_id'      => $invoice->id,
            'status'          => $invoice->status,
            'previous_status' => $invoice->status,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        InvoiceHistory::create([
            'invoice_id' => $invoice->id,
            'text'       => 'Invoice has been created.'
        ]);

        $invoice->user()->notifyNewInvoice($invoice);

        return $invoiceStatusHistory->saveOrFail();
    }
}