<?php

declare(strict_types=1);

namespace App\Services\Balance;

use App\Models\WalletBalance;
use Illuminate\Database\Connection;
use Throwable;

class WalletBalanceService
{
    public function __construct(private readonly Connection $db)
    {
    }

    public function increment(WalletBalance $walletBalance, string $amount): void
    {
        try {
            $this->db->beginTransaction();

            $this->db->update(
                'UPDATE wallet_balances SET balance = balance + :amount WHERE wallet_id = :walletId AND currency_id = :currencyId',
                [
                    'amount' => (float)$amount,
                    'walletId' => $walletBalance->wallet_id,
                    'currencyId' => $walletBalance->currency_id,
                ]
            );

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();

            throw $e;
        }
    }

    public function decrement(WalletBalance $walletBalance, string $amount): void
    {
        try {
            $this->db->beginTransaction();

            $this->db->update(
                'UPDATE wallet_balances SET balance = balance - :amount WHERE wallet_id = :walletId AND currency_id = :currencyId',
                [
                    'amount' => (float)$amount,
                    'walletId' => $walletBalance->wallet_id,
                    'currencyId' => $walletBalance->currency_id,
                ]
            );

            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();

            throw $e;
        }
    }
}