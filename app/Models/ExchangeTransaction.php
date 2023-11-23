<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * ExchangeTransaction
 */
class ExchangeTransaction extends Model
{
	/**
	 * @var string[]
	 */
	protected $fillable = [
        'user_id',
        'wallet_id',
        'from_currency_id',
        'to_currency_id',
        'amount',
        'amount_usd',
        'left_amount',
    ];

	/**
	 * @return HasOne
	 */
	public function user(): HasOne
	{
		return $this->hasOne(User::class, 'id', 'user_id');
	}
}
