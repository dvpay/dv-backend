<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\CurrencyId;
use App\Enums\TimeRange;
use App\Enums\TransactionType;
use App\Models\ExchangeColdWalletWithdrawal;
use App\Models\ExchangeTransaction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Collection;

/**
 * DashboardService
 */
class DashboardService
{
	/**
	 * @param User       $user
	 * @param array|null $stores
	 * @param TimeRange  $timeRange
	 *
	 * @return Collection
	 */
	public function getDepositTransactions(User $user, ?array $stores, TimeRange $timeRange): Collection
	{
		$timezone = $user->location ?? '+00:00';

		$transactionsQuery = Transaction::selectRaw(
			"CONVERT_TZ(transactions.created_at, '+00:00', '$timezone') as created_at,
            transactions.network_created_at as network_created_at,
            invoices.id as invoiceId,
            invoices.custom as custom,
            invoices.description as description,
            stores.name as storeName,
            transactions.amount_usd as amountUsd,
            transactions.amount as amount,
            transactions.tx_id as tx,
            transactions.currency_id as currencyId"
		)
		                                ->join('stores', 'stores.id', 'transactions.store_id')
		                                ->join('invoices', 'invoices.id', 'transactions.invoice_id')
		                                ->where([
			                                ['stores.user_id', $user->id],
			                                ['transactions.type', TransactionType::Invoice],
		                                ]);

		if ($stores) {
			$transactionsQuery->whereIn('transactions.store_id', $stores);
		}

		if ($timeRange->value === TimeRange::Day->value) {
			$transactionsQuery->whereRaw("DATE(CONVERT_TZ(invoices.created_at, '+00:00', '$timezone')) = CURRENT_DATE");
		} elseif ($timeRange->value === TimeRange::Month->value) {
			$transactionsQuery->whereRaw("DATE(CONVERT_TZ(invoices.created_at, '+00:00', '$timezone')) > CURRENT_DATE - INTERVAL 30 DAY");
		} else {
			$transactionsQuery->limit(15);
		}

		return $transactionsQuery->orderBy('transactions.created_at', 'desc')
		                         ->get();
	}

}
