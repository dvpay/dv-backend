<?php

namespace App\Listeners;

use App\Enums\InvoiceStatus;
use App\Events\InvoiceStatusUpdatedEvent;
use App\Jobs\WebhookJob;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * SendWebhookListener
 */
class SendWebhookListener implements ShouldQueue
{
    public function __construct(
            public bool $newLogic
    ) {
    }

    /**
     * @param  InvoiceStatusUpdatedEvent  $event
     * @return void
     */
    public function handle(InvoiceStatusUpdatedEvent $event): void
    {
        $invoice = $event->invoice;

        if ($this->newLogic) {
            if (
                    in_array($invoice->status, [InvoiceStatus::Paid, InvoiceStatus::OverPaid])
                    || ($invoice->status == InvoiceStatus::PartiallyPaid && $invoice->is_confirm)
                    || $invoice->status == InvoiceStatus::PartiallyPaidExpired
            ) {
                WebhookJob::dispatch($event->invoice);
            }
        } else {
            WebhookJob::dispatch($event->invoice);
        }
    }
}
