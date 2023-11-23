<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\TransactionType;
use App\Events\TransactionCreatedEvent;
use App\Jobs\TelegramNotificationJob;

/**
 * SendTransactionTelegramNotificationListener
 */
class SendTransactionTelegramNotificationListener
{
    /**
     * @param TransactionCreatedEvent $event
     * @return void
     */
    public function handle(TransactionCreatedEvent $event): void
    {
        $transaction = $event->transaction;

        match ($transaction->type) {
            TransactionType::Invoice => $transaction->user->notifyReceivingPayment($transaction),
            TransactionType::Transfer => $transaction->user->notifyTransfer($transaction),
        };
    }
}