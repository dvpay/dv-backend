<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeWithdrawalWallet extends Model
{

    use HasFactory;

    protected $fillable = [
        'address',
        'is_withdrawal_enable',
        'min_balance',
        'chain',
        'currency',
        'user_id',
        'exchange_id',
        'current_balance'
    ];

    protected $casts = [
      'is_withdrawal_enable' =>  'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
