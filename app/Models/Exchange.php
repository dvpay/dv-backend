<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ExchangeService;

class Exchange extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'url',
        'is_active',
    ];

    protected $casts = [
        'slug' => ExchangeService::class,
        'is_active' => 'boolean',
    ];
}