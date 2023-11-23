<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WithdrawalWalletAddress extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'withdrawal_wallet_id',
        'address',
    ];
}
