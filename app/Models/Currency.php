<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Blockchain;
use App\Enums\CurrencySymbol;

class Currency extends Model
{
    public $incrementing = false;

    protected $casts = [
        'name' => CurrencySymbol::class,
        'code' => CurrencySymbol::class,
        'blockchain' => Blockchain::class,
        'is_fiat' => 'boolean',
        'isFiat' => 'boolean',
        'has_balance' => 'boolean',
        'chain' => 'string',
    ];
}