<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WithdrawalWalletAddress extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'withdrawal_wallet_id',
        'address',
    ];
}
