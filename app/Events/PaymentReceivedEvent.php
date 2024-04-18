<?php

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Foundation\Events\Dispatchable;

class PaymentReceivedEvent
{
    use Dispatchable;

    public function __construct(
        public readonly Transaction $transaction,
        public readonly int         $delay = 0,
        public readonly int         $attempts = 1,
    )
    {
    }
}
