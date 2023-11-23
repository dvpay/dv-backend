<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Blockchain;
use App\Events\InvoiceAddressUpdateEvent;
use App\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InvoiceAddress extends Model
{
    use HasUuid;

    protected $fillable = [
        'invoice_id',
        'address',
        'blockchain',
        'watch_id',
        'currency_id',
        'balance',
        'rate',
        'invoice_currency_id',
        'exchange_rate_at'
    ];

    protected $casts = [
        'blockchain'       => Blockchain::class,
        'exchange_rate_at' => 'datetime'
    ];

    protected $dispatchesEvents = [
        'updated' => InvoiceAddressUpdateEvent::class
    ];

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'id', 'invoice_id');
    }

    public function currency(): HasOne
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }

    public function exchangeRateConversion(): bool
    {
        if (now()->diffInMinutes($this->exchange_rate_at) >= config('setting.exchange_rate_time')) {
            return true;
        }
        return false;
    }
}