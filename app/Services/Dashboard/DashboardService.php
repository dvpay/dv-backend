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

	/**
	 * @param User|Authenticatable $user
	 *
	 * @return array|array[]
	 */
	public function getSavedOnCommissionStats(User|Authenticatable $user): array
	{
		$results = [
			TimeRange::Today->value     => [],
			TimeRange::Yesterday->value => [],
			TimeRange::Month->value     => [],
		];

		$timezone = $user->location ?? '+00:00';

		foreach ($results as $period => $result) {

			$results[$period]['amountUsd'] = 0;

			$query = Transaction::selectRaw('
				transactions.currency_id,
				SUM(CASE WHEN transactions.type = "invoice" THEN 1 ELSE 0 END) AS incoming_transactions,
				SUM(CASE WHEN transactions.type = "transfer" THEN 1 ELSE 0 END) AS outcoming_transactions
			');

			$query->whereUserId($user->id);

			switch ($period) {
				case TimeRange::Today->value:
					$query->whereRaw("DATE(CONVERT_TZ(transactions.created_at, '+00:00', '$timezone')) = CURRENT_DATE");
					break;
				case TimeRange::Yesterday->value:
					$query->whereRaw("DATE(CONVERT_TZ(transactions.created_at, '+00:00', '$timezone')) = CURRENT_DATE - INTERVAL 1 DAY");
					break;
				case TimeRange::Month->value:
					$query->whereRaw("DATE(CONVERT_TZ(transactions.created_at, '+00:00', '$timezone')) > CURRENT_DATE - INTERVAL 30 DAY");
					break;
			}

			$transactions = $query->groupBy('transactions.currency_id')->get()->toArray();

			if (!empty($transactions)) {
				foreach ($transactions as $transaction) {

					$commission = match ($transaction['currency_id']) {  // USD
						CurrencyId::UsdtTron->value => 0.62,
						CurrencyId::BtcBitcoin->value => 0.8,
						default => 0.5
					};

					$savedTransactions = $transaction['incoming_transactions'] - $transaction['outcoming_transactions'];
					$savedTransactions = (int)$savedTransactions;

					if ($savedTransactions > 0) {
						$results[$period]['amountUsd'] += round($savedTransactions * $commission, 2);
					}
				}
			}
		}

		return $results;
	}

	/**
	 * @param User|Authenticatable $user
	 *
	 * @return array|array[]
	 */
	public function getGetExchangedStats(User|Authenticatable $user): array
	{
		$results = [
			TimeRange::Today->value     => [],
			TimeRange::Yesterday->value => [],
			TimeRange::Month->value     => [],
		];

		$timezone = $user->location ?? '+00:00';

		foreach ($results as $period => $result) {

			$results[$period]['amountUsd'] = 0;

			$query = ExchangeTransaction::selectRaw('
				SUM(amount_usd) AS amount_usd_sum
			');

			$query->whereUserId($user->id);

			switch ($period) {
				case TimeRange::Today->value:
					$query->whereRaw("DATE(CONVERT_TZ(exchange_transactions.created_at, '+00:00', '$timezone')) = CURRENT_DATE");
					break;
				case TimeRange::Yesterday->value:
					$query->whereRaw("DATE(CONVERT_TZ(exchange_transactions.created_at, '+00:00', '$timezone')) = CURRENT_DATE - INTERVAL 1 DAY");
					break;
				case TimeRange::Month->value:
					$query->whereRaw("DATE(CONVERT_TZ(exchange_transactions.created_at, '+00:00', '$timezone')) > CURRENT_DATE - INTERVAL 30 DAY");
					break;
			}

			$exchangeTransactions = $query->get()->toArray();

			if (!empty($exchangeTransactions)) {
				foreach ($exchangeTransactions as $exchangeTransaction) {
					$results[$period]['amountUsd'] = round((float)$exchangeTransaction['amount_usd_sum'], 2);
				}
			}
		}

		return $results;
	}

    /**
     * @param User|Authenticatable $user
     *
     * @return array|array[]
     */
    public function getExchangeColdWalletWithdrawalsStats(User|Authenticatable $user): array
    {
        $results = [
            TimeRange::Today->value     => [],
            TimeRange::Yesterday->value => [],
            TimeRange::Month->value     => [],
        ];

        $timezone = $user->location ?? '+00:00';
        $partQuery = "DATE(CONVERT_TZ(ecww.created_at, '+00:00', '$timezone'))";

        foreach ($results as $period => $result) {
            $results[$period]['amountUsd'] = 0;

            $query = ExchangeColdWalletWithdrawal::from('exchange_cold_wallet_withdrawals AS ecww')
                ->selectRaw('SUM(amount) AS amount_usd_sum')
                ->join('exchange_cold_wallets AS ecw', 'ecww.exchange_cold_wallet_id', '=', 'ecw.id')
                ->join('wallets AS w', 'ecw.wallet_id', '=', 'w.id')
                ->where('w.user_id', '=', $user->id);

            switch ($period) {
                case TimeRange::Today->value:
                    $query->whereRaw("$partQuery = CURRENT_DATE");
                    break;
                case TimeRange::Yesterday->value:
                    $query->whereRaw("$partQuery = CURRENT_DATE - INTERVAL 1 DAY");
                    break;
                case TimeRange::Month->value:
                    $query->whereRaw("$partQuery > CURRENT_DATE - INTERVAL 30 DAY");
                    break;
            }

            $rows = $query->get()->toArray();
            foreach ($rows as $row) {
                $results[$period]['amountUsd'] = round((float)$row['amount_usd_sum'], 2);
            }
        }

        return $results;
    }
}
