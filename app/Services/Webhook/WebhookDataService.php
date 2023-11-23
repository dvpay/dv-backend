<?php

declare(strict_types=1);

namespace App\Services\Webhook;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Repositories\TransactionRepository;

class WebhookDataService
{
    public function __construct(private readonly TransactionRepository $transactionRepository)
    {
    }

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
}