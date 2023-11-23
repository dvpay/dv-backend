<?php

namespace App\Events;

use App\Models\InvoiceAddress;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class InvoiceAddressUpdateEvent
{
    use Dispatchable, InteractsWithQueue, Queueable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly InvoiceAddress $invoiceAddress)
    {
    }
}
