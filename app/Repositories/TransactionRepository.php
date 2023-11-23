<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository
{
    public function getTransactionsByInvoiceId(string $invoiceId): ?Collection
    {
        return Transaction::select(
            'transactions.tx_id as txId',
            'transactions.created_at as createdAt',
            'currencies.code as currency',
            'currencies.blockchain',
            'transactions.amount',
            'transactions.amount_usd as amountUsd',
            'transactions.rate'
        )->join('currencies', 'transactions.currency_id', '=', 'currencies.id')
        ->where('invoice_id', $invoiceId)
        ->get();
    }

    public function getTransactionsSumByInvoiceId(string $invoiceId): ?string
    {
        return (string)Transaction::where('invoice_id', $invoiceId)->sum('amount_usd');
    }

    public function getByTxId(string $txId)
    {
        return Transaction::where('tx_id', $txId)->first();
    }
}