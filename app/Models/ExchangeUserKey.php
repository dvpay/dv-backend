<?php

declare(strict_types=1);

namespace App\Models;



use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeUserKey extends Model
{
    protected $fillable = [
        'user_id',
        'key_id',
        'value',
    ];

    public function exchangeKey(): BelongsTo
    {
        return $this->belongsTo(ExchangeKey::class, 'key_id');
    }
}