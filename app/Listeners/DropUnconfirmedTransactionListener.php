<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TransactionCreatedEvent;
use App\Models\UnconfirmedTransaction;

/**
 * DropUnconfirmedTransactionListener
 */
class DropUnconfirmedTransactionListener
{
    /**
     * @param TransactionCreatedEvent $event
     * @return void
     */
    public function handle(TransactionCreatedEvent $event): void
    {
        $transaction = $event->transaction;

        $unconfirmedTransaction = UnconfirmedTransaction::where('tx_id', $transaction->tx_id)->first();
        if (!$unconfirmedTransaction) {
            return;
        }

        $unconfirmedTransaction->delete();
    }
}