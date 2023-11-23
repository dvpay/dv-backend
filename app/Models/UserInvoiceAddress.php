<?php

namespace App\Models;

use App\Enums\Blockchain;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * UserInvoiceAddress
 */
class UserInvoiceAddress extends Model
{
    use HasFactory;

	/**
	 * @var string[]
	 */
	protected $fillable = [
        'state',
        'processing_owner_id',
        'blockchain',
        'address',
        'watch_id',
        'balance',
        'balance_usd',
        'currency_id',
    ];

	/**
	 * @var string[]
	 */
	protected $casts = [
        'blockchain' => Blockchain::class,
    ];

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('id', $value)
            ->orWhere('address', $value)
            ->firstOrFail();
    }

    public function invoices(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(Invoice::class, InvoiceAddress::class, 'address', 'id', 'address', 'invoice_id');
    }


    public function transactionsIncoming(): \Illuminate\Database\Eloquent\Relations\hasMany
    {
        return $this->hasMany(Transaction::class, 'to_address', 'address');
    }

    public function transactionsOutgoing(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class, 'from_address', 'address');

    }
}