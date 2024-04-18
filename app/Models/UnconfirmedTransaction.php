<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class UnconfirmedTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'store_id',
        'invoice_id',
        'from_address',
        'to_address',
        'tx_id',
        'currency_id',
        'amount',
        'amount_usd',
        'payer_id'
    ];

    public function payer(): BelongsTo
    {
        return $this->belongsTo(Payer::class);
    }

    public function currency(): HasOne
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }

    public function store(): HasOne
    {
        return $this->hasOne(Store::class, 'id', 'store_id');
    }

}
