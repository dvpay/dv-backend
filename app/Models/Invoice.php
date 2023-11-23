<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Events\InvoiceCreatedEvent;
use App\Events\InvoiceStatusUpdatedEvent;
use App\Models\Traits\HasUuidWithSlug;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Invoice model.
 *
 * @property string $id
 * @property string $slug
 * @property InvoiceStatus $status
 * @property string $store_id
 * @property string $order_id
 * @property string $currency_id
 * @property float $amount
 * @property string $description
 * @property string $return_url
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $expired_at
 * @property string $destination
 * @property string $custom
 * @property int $attached_by
 * @property string $attached_at
 * @property bool $is_confirm
 * @property string $confirmed_at
 * @property string $payer_email
 * @property string $payer_language
 *
 * @property Store $store
 * @property InvoiceAddress[] $addresses
 * @property Currency $currency
 * @property Webhook[] $webhooks
 * @property Transaction[] $transactions
 * @property InvoiceStatusHistory[] $statuses
 */
class Invoice extends Model
{
    use HasFactory, HasUuidWithSlug;

    /**
     * @var string[]
     */
    protected $fillable = [
        'slug',
        'status',
        'store_id',
        'order_id',
        'currency_id',
        'amount',
        'description',
        'return_url',
        'success_url',
        'expired_at',
        'destination',
        'custom',
        'attached_at',
        'attached_by',
        'is_confirm',
        'confirmed_at',
        'payer_email',
        'payer_language',
        'payer_id',
        'ip',
        'user_agent'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'status'     => InvoiceStatus::class,
        'created_at' => 'datetime:DATE_ATOM',
        'updated_at' => 'datetime:DATE_ATOM',
        'expired_at' => 'datetime:DATE_ATOM',
        'amount'     => 'float',
        'is_confirm' => 'boolean',
        'custom'     => 'json'
    ];

    /**
     * @var string[]
     */
    protected $dispatchesEvents = [
        'created' => InvoiceCreatedEvent::class,
        //'updated' => InvoiceStatusUpdatedEvent::class,
    ];

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('slug', '=', $value)
            ->orWhere($this->getKeyName(), '=', $value)
            ->first();
    }

    /**
     * @return HasOne
     */
    public function store(): HasOne
    {
        return $this->hasOne(Store::class, 'id', 'store_id');
    }

    /**
     * @return HasMany
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(InvoiceAddress::class, 'invoice_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function currency(): HasOne
    {
        return $this->hasOne(Currency::class, 'id', 'currency_id');
    }

    /**
     * @return HasMany
     */
    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class, 'store_id', 'store_id');
    }

    /**
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'invoice_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function statuses(): HasMany
    {
        return $this->hasMany(InvoiceStatusHistory::class, 'invoice_id', 'id');
    }

    /**
     * @throws Exception
     */
    public function updateStatus(InvoiceStatus $status): void
    {
        $this->status = $status;
        if (!$this->save()) {
            throw new Exception(__("Invoice status doesn't update"));
        }
        event(new InvoiceStatusUpdatedEvent($this));
    }

    public function user()
    {
        return $this?->store?->user;
    }

    public function payer()
    {
        return $this->belongsTo(Payer::class, 'payer_id', 'id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(InvoiceHistory::class, 'invoice_id', 'id');
    }
}
