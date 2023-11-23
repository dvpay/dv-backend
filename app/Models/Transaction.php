<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TransactionType;
use App\Events\TransactionCreatedEvent;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasUuid;

    public $incrementing = false;

    protected $casts = [
        'type'                 => TransactionType::class,
        'withdrawal_is_manual' => 'boolean',
    ];

    protected $fillable = [
        'user_id',
        'store_id',
        'invoice_id',
        'currency_id',
        'tx_id',
        'type',
        'from_address',
        'to_address',
        'amount',
        'amount_usd',
        'rate',
        'fee',
        'withdrawal_is_manual',
        'network_created_at',
        'energy',
        'bandwidth',
    ];

    protected $dispatchesEvents = [
        'created' => TransactionCreatedEvent::class
    ];

    public function currency(): HasOne
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'id', 'invoice_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function hotWallet(): BelongsTo
    {
        return $this->belongsTo(HotWallet::class, 'to_address', 'address')
            ->orWhere(function ($query) {
                $query->where('from_address', $this->to_address);
            });
    }
}
