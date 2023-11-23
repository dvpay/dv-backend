<?php

declare(strict_types=1);

namespace App\Models;

class UnconfirmedTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'store_id',
        'invoice_id',
        'from_address',
        'to_address',
        'tx_id',
        'currency_id',
    ];
}
