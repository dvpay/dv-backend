<?php

namespace App\Models;

use App\Enums\Blockchain;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayerAddress extends Model
{
    use SoftDeletes, HasUuid, HasFactory;

    protected $fillable = [
            'payer_id',
            'currency_id',
            'blockchain',
            'address',
    ];

    protected $casts = [
            'blockchain' => Blockchain::class,
    ];

    public function payer(): BelongsTo
    {
        return $this->belongsTo(Payer::class, 'payer_id', 'id');
    }

    public function currency(): HasOne
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'to_address', 'address');
    }

    public function unconfirmed_transactions(): HasMany
    {
        return $this->hasMany(UnconfirmedTransaction::class, 'to_address', 'address');
    }


    public function lastTransactions(): HasMany
    {
        return $this->transactions()->where('created_at', '>=', now()->subMonth());
    }

    public function lastUnconfirmedTransactions(): HasMany
    {
        return $this->unconfirmed_transactions()->where('created_at', '>=', now()->subMonth());

    }
}
