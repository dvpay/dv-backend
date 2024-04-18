<?php

namespace App\Observers;

use App\Enums\Metric;
use App\Facades\Prometheus;
use App\Models\Transaction;

class TransactionObserver
{
    public function created(Transaction $transaction): void
    {
        Prometheus::counterInc(
            name: Metric::BackendTransactionCreated->getName(),
            labels: [$transaction->type->value]
        );
    }

    public function creating(Transaction $transaction): void
    {
        $transaction->created_at_index = time();
    }
}
