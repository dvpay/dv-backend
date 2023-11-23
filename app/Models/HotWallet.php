<?php

namespace App\Models;

use App\Enums\Blockchain;
use App\Enums\HotWalletState;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HotWallet extends Model
{
    protected $fillable = [
        'currency_id',
        'user_id',
        'address',
        'blockchain',
        'state',
        'amount',
        'amount_usd'
    ];

    protected $casts = [
        'blockchain' => Blockchain::class,
        'state'      => HotWalletState::class,
    ];

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('id', $value)
            ->orWhere('address', $value)
            ->firstOrFail();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'to_address', 'address')
            ->orWhere(function ($query) {
                $query->where('from_address', $this->address);
            });
    }

    public function currency(): HasOne
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }

    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(Invoice::class, InvoiceAddress::class, 'address', 'id', 'address', 'invoice_id');
    }

    public function transactionsIncoming(): HasMany
    {
        return $this->hasMany(Transaction::class, 'to_address', 'address');
    }

    public function transactionsOutgoing(): HasMany
    {
        return $this->hasMany(Transaction::class, 'from_address', 'address');
    }

    public function latestTransaction()
    {
        return $this->transactionsOutgoing()
            ->where('type', TransactionType::Transfer->value)
            ->latest()
            ->take(1);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
