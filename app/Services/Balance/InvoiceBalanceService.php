<?php

declare(strict_types=1);

namespace App\Services\Balance;

use App\Models\InvoiceAddress;
use Illuminate\Database\Connection;
use Throwable;

class InvoiceBalanceService
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function increment(InvoiceAddress $invoiceAddress, string $amount): void
    {
        try {
            $this->db->beginTransaction();

            $this->db->update(
                'UPDATE invoice_addresses SET balance = balance + :amount WHERE invoice_id = :invoiceId AND currency_id = :currencyId',
                [
                    'amount' => (float)$amount,
                    'invoiceId' => $invoiceAddress->invoice_id,
                    'currencyId' => $invoiceAddress->currency_id,
                ]
            );

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();

            throw $e;
        }
    }

    public function decrement(InvoiceAddress $invoiceAddress, string $amount): void
    {
        try {
            $this->db->beginTransaction();

            $this->db->update(
                'UPDATE invoice_addresses SET balance = balance - :amount WHERE invoice_id = :invoiceId AND currency_id = :currencyId',
                [
                    'amount' => (float)$amount,
                    'invoiceId' => $invoiceAddress->invoice_id,
                    'currencyId' => $invoiceAddress->currency_id,
                ]
            );

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();

            throw $e;
        }
    }
}