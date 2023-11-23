<?php

namespace App\Models;

/**
 * ExchangeColdWalletWithdrawal
 */
class ExchangeColdWalletWithdrawal extends Model
{
	/**
	 * @var string[]
	 */
	protected $fillable = [
		'exchange_cold_wallet_id',
		'address',
		'amount',
		'exchange_id',
	];
}
