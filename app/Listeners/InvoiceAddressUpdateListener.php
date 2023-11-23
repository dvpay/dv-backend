<?php

namespace App\Listeners;

use App\Events\InvoiceAddressUpdateEvent;
use App\Models\InvoiceHistory;

class InvoiceAddressUpdateListener
{
    public function __construct()
    {
    }

    public function handle(InvoiceAddressUpdateEvent $event): void
    {
        $invoice = $event->invoiceAddress;

        if (!$invoice->isDirty('address')) {
            return;
        }

        $message = 'Invoice address update: :originalAddress -> :currentAddress for currency: :currency';

        $variables = [
            'originalAddress' => $invoice->getOriginal('address'),
            'currentAddress'  => $invoice->address,
            'currency'        => $invoice->currency_id
        ];

        if ($invoice->getOriginal('address') === '') {
            $message = 'Invoice get address: :currentAddress for currency: :currency';
            $variables = [
                "currentAddress" => $invoice->address,
                "currency"       => $invoice->currency_id
            ];
        }

        InvoiceHistory::create([
            'invoice_id'     => $event->invoiceAddress->invoice_id,
            'text'           => $message,
            'text_variables' => $variables
        ]);
    }
}
