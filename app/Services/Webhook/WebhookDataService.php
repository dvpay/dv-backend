<?php

declare(strict_types=1);

namespace App\Services\Webhook;

use App\Enums\InvoiceStatus;
use App\Enums\WebhookType;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\UnconfirmedTransaction;
use App\Repositories\TransactionRepository;
use JetBrains\PhpStorm\Deprecated;

class WebhookDataService
{
    public function __construct(private readonly TransactionRepository $transactionRepository)
    {
    }

    #[Deprecated]
    public function getWebhookData(Invoice $invoice): array
    {
        $paidAt = null;
        if (in_array($invoice->status, [InvoiceStatus::Paid, InvoiceStatus::Success])) {
            $paidAt = $invoice->updated_at;
        }

        $transactions = $this->transactionRepository->getTransactionsByInvoiceId($invoice->id);
        $receiveAmount = $this->transactionRepository->getTransactionsSumByInvoiceId($invoice->id);

        $status = $invoice->status;
        if ($status === InvoiceStatus::Success) {
            $status = InvoiceStatus::Paid;
        }

        $result = [
            'orderId'        => $invoice->order_id,
            'status'         => $status->value,
            'createdAt'      => $invoice->created_at,
            'paidAt'         => $paidAt,
            'expiredAt'      => $invoice->expired_at,
            'amount'         => $invoice->amount,
            'receivedAmount' => $receiveAmount,
            'transactions'   => $transactions,
            'payer'          => $invoice->payer_id ?
                [
                    'id'          => $invoice->payer->id,
                    'storeUserId' => $invoice->payer->store_user_id
                ]
                : null,
        ];

        return $result;
    }

    public function getTransactionData(Transaction $transaction): array
    {
        return [
            'type'           => WebhookType::PaymentReceived->value,
            'orderId'        => '',
            'status'         => InvoiceStatus::Paid->value,
            'createdAt'      => $transaction->created_at,
            'paidAt'         => $transaction->created_at,
            'expiredAt'      => $transaction->created_at,
            'amount'         => $transaction->amount_usd,
            'receivedAmount' => $transaction->amount_usd,
            'transactions'   => [
                [
                    'txId'       => $transaction->tx_id,
                    'createdAt'  => $transaction->created_at,
                    'currency'   => $transaction->currency->code,
                    'blockchain' => $transaction->currency->blockchain,
                    'amount'     => $transaction->amount,
                    'amountUsd'  => $transaction->amount_usd,
                    'rate'       => $transaction->rate
                ]
            ],
            'payer'          => $transaction->payer_id ?
                [
                    'id'          => $transaction->payer_id,
                    'storeUserId' => $transaction->payer->store_user_id
                ]
                : null,
        ];
    }

    public function getUnconfirmedWebhookData(UnconfirmedTransaction $transaction): array
    {

        return [
            'type'                    => WebhookType::UnconfirmedTransaction->value,
            'unconfirmed_orderId'     => '',
            'unconfirmed_status'      => InvoiceStatus::WaitingConfirmations->value,
            'unconfirmed_paidAt'      => null,
            'unconfirmed_amount'      => $transaction->amount,
            'unconfirmed_transaction' => [
                'txId'       => $transaction->tx_id,
                'createdAt'  => $transaction->created_at,
                'currency'   => $transaction->currency->code,
                'blockchain' => $transaction->currency->blockchain,
                'amount'     => $transaction->amount,
                'amountUsd'  => $transaction->amount_usd,
            ],
            'unconfirmed_payer'       => $transaction->payer_id ?
                [
                    'unconfirmed_id'          => $transaction->payer->id,
                    'unconfirmed_storeUserId' => $transaction->payer->store_user_id
                ]
                : null,

        ];

    }
}