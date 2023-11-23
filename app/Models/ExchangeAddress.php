<?php

namespace App\Models;

use App\Enums\ExchangeAddressType;
use Illuminate\Database\Eloquent\Model;

class ExchangeAddress extends Model
{
    protected $fillable = [
        'user_id',
        'exchange_key',
        'currency',
        'exchange_user_id',
        'address',
        'chain',
        'address_type'
    ];

    protected $casts = [
        'address_type' => ExchangeAddressType::class
    ];
}
