<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeWithdrawalWallet extends Model
{
    protected $fillable = [
        'address',
        'is_withdrawal_enable',
        'min_balance',
        'chain',
        'currency',
        'user_id',
        'exchange_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
