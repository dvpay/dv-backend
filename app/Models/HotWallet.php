<?php

namespace App\Models;

use App\Enums\Blockchain;
use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class HotWallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency_id',
        'user_id',
        'address',
        'blockchain',
        'amount',
        'amount_usd'
    ];

    protected $casts = [
        'blockchain' => Blockchain::class,
    ];

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('id', $value)
            ->orWhere('address', $value)
            ->firstOrFail();
    }

    public function allTransactions(): HasMany
    {
        $incoming = $this->transactionsIncoming();
        $outgoing = $this->transactionsOutgoing();

        return $incoming->union($outgoing);
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

    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class);
    }
}
