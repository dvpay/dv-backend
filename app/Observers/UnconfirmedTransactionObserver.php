<?php

namespace App\Observers;

use App\Enums\Metric;
use App\Facades\Prometheus;
use App\Models\UnconfirmedTransaction;

class UnconfirmedTransactionObserver
{
    public function created(UnconfirmedTransaction $unconfirmedTransaction): void
    {
        Prometheus::counterInc(name: Metric::BackendUnconfirmedTransactionCreated->getName());
    }
}
