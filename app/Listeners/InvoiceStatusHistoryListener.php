<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\InvoiceStatusUpdatedEvent;
use App\Models\InvoiceHistory;
use App\Models\InvoiceStatusHistory;

/**
 * InvoiceStatusHistoryListener
 */
class InvoiceStatusHistoryListener
{
    /**
     * @param InvoiceStatusUpdatedEvent $event
     * @return bool
     */
    public function handle(InvoiceStatusUpdatedEvent $event): bool
    {
        $invoice = $event->invoice;

        if (!$invoice->isDirty('status')) {
            return false;
        }

        $oldStatus = $invoice->getOriginal('status');

        $invoiceStatusHistory = new InvoiceStatusHistory([
            'invoice_id'      => $invoice->id,
            'status'          => $invoice->status,
            'previous_status' => $oldStatus,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        InvoiceHistory::create([
            'invoice_id'     => $invoice->id,
            'text'           => 'Invoice status updated :statusOriginal -> :status',
            'text_variables' => [
                'statusOriginal' => $invoice->getOriginal('status')->getValue(),
                'status'         => $invoice->status->getValue(),
            ]
        ]);


        return $invoiceStatusHistory->save();
    }
}