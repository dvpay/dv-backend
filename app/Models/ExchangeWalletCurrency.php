<?php

declare(strict_types=1);

namespace App\Models;

class ExchangeWalletCurrency extends Model
{
    protected $fillable = [
        'wallet_id',
        'from_currency_id',
        'to_currency_id',
        'via',
        'chain'
    ];
}