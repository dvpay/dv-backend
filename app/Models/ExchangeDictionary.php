<?php

declare(strict_types=1);

namespace App\Models;

class ExchangeDictionary extends Model
{

    protected $fillable = [
        'exchange_id',
        'from_currency_id',
        'to_currency_id',
        'min_quantity',
        'decimals',
    ];
}