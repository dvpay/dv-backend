<?php

namespace App\Models;

/**
 * ExchangeColdWallet
 */
class ExchangeColdWallet extends Model
{
    /**
     * @var string[]
     */
    protected $fillable = [
        'wallet_id',
        'address',
        'is_withdrawal_enabled',
        'withdrawal_min_balance',
        'chain',
        'currency'
    ];

    protected $casts = [
        'is_withdrawal_enabled' => 'boolean'
    ];

    /**
     * Returns the withdrawal wallet status.
     *
     * @return bool
     */
    public function isWithdrawalEnabled(): bool
    {
        return !empty($this->address) && (bool)$this->is_withdrawal_enabled;
    }
}
