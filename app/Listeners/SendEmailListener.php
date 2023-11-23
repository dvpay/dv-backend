<?php

namespace App\Listeners;

use App\Enums\InvoiceStatus;
use App\Events\InvoiceStatusUpdatedEvent;
use App\Mail\User\InvoicePaid;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailer;

/**
 * SendWebhookListener
 */
class SendEmailListener implements ShouldQueue
{
    public function __construct(
        private readonly Mailer $mailer
    )
    {
    }

    /**
     * @param InvoiceStatusUpdatedEvent $event
     * @return void
     */
    public function handle(InvoiceStatusUpdatedEvent $event): void
    {
        $invoice = $event->invoice;

        if (
            $invoice->status == InvoiceStatus::Paid
            && $invoice->payer_email
        ) {
            $store = $invoice->store;
            $resInvoice = [
                'invoiceId' => $invoice->id,
                'createdAt' => $invoice->created_at,
                'expiredAt' => $invoice->expired_at,
                'amount' => $invoice->amount,
                'status' => $invoice->status->value,
                'store' => $store->name,
                'storeUrl' => $store->site,
                'description' => $invoice->description,
                'supportEmail' => config('mail.support.email')
            ];

            $transactions = Transaction::select('currency_id as currencyId', 'tx_id as txId', 'amount')
                ->where('invoice_id', $invoice->id)
                ->get()
                ->toArray();

            $this->mailer->to($invoice->payer_email)
                ->send(new InvoicePaid($resInvoice, $transactions));
        }
    }
}
