<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ExchangeKeyType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeKey extends Model
{
    protected $fillable = [
        'exchange_id',
        'key',
    ];

    protected $casts = [
        'key' => ExchangeKeyType::class,
    ];

    public function exchangeService(): BelongsTo
    {
        return $this->belongsTo(Exchange::class, 'exchange_id');
    }

}