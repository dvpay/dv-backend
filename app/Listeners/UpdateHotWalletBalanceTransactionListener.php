<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\TransactionType;
use App\Events\TransactionCreatedEvent;
use App\Models\HotWallet;
use App\Models\Transaction;


class UpdateHotWalletBalanceTransactionListener
{
    /**
     * @param TransactionCreatedEvent $event
     * @return void
     */
    public function handle(TransactionCreatedEvent $event): void
    {
        $transaction = $event->transaction;


        $addressField = TransactionType::Transfer === $transaction->type ?  'from_address' : 'to_address';

        $sumTransferAmount = Transaction::query()
            ->where([ 'from_address' => $transaction->$addressField])
            ->sum('amount') ?? 0;
        $sumTransferAmountUsd = Transaction::query()
            ->where([ 'from_address' => $transaction->$addressField])
            ->sum('amount_usd') ?? 0;

        $sumPaymentAmount = Transaction::query()
            ->where([ 'to_address' => $transaction->$addressField])
            ->sum('amount') ?? 0;
        $sumPaymentAmountUsd = Transaction::query()
            ->where([ 'to_address' => $transaction->$addressField])
            ->sum('amount_usd') ?? 0;

        HotWallet::where('address', $transaction->$addressField)
            ->update([
                'amount' => $sumPaymentAmount - $sumTransferAmount,
                'amount_usd' => $sumPaymentAmountUsd - $sumTransferAmountUsd,
            ]);
    }
}