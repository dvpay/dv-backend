<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\RateSource as RateSourceEnum;
use App\Models\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property string $id
 * @property int $user_id
 * @property string $name
 * @property string $site
 * @property string $currency_id
 * @property RateSourceEnum $rate_source
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 * @property int $invoice_expiration_time
 * @property string $processing_owner_id
 * @property string $return_url
 * @property string $success_url
 * @property int $address_hold_time
 * @property string $rate_scale
 * @property int $status
 */
class Store extends Model
{
    use HasUuid;

    protected $fillable = [
            'user_id',
            'name',
            'site',
            'currency_id',
            'rate_source',
            'rate_scale',
            'invoice_expiration_time',
            'processing_owner_id',
            'return_url',
            'success_url',
            'address_hold_time',
            'status',
            'static_addresses',
    ];

    protected $casts = [
            'rate_source'    => RateSourceEnum::class,
            'rate_scale'     => 'string',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function currency(): HasOne
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class, 'store_id', 'id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'store_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'stores_users');
    }

    public function invoicesSuccess(): HasMany
    {
        return $this->invoices()->whereIn('status', [InvoiceStatus::Paid->value, InvoiceStatus::PartiallyPaid->value]);
    }
}
