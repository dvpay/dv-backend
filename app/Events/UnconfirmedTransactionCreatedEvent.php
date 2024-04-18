<?php

namespace App\Events;

use App\Models\UnconfirmedTransaction;
use Illuminate\Foundation\Events\Dispatchable;

class UnconfirmedTransactionCreatedEvent
{
    use Dispatchable;

    public function __construct(
        public readonly UnconfirmedTransaction $unconfirmedTransaction,
        public readonly int                    $delay = 0,
        public readonly int                    $attempts = 1,
    )
    {

    }
}
