<?php

declare(strict_types=1);

namespace App\Services\Dashboard;

use App\Enums\TimeRange;
use App\Enums\TransactionType;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\User;
use DateInterval;
use DatePeriod;
use DateTime;

/**
 * DepositSummaryService
 */
class DepositSummaryService
{
	/**
	 * @param User       $user
	 * @param TimeRange  $timeRange
	 * @param array|null $stores
	 *
	 * @return array
	 */
	public function getDepositSummary(User $user, TimeRange $timeRange, ?array $stores): array
    {
        date_default_timezone_set($user->location ?? 'UTC');

        if ($timeRange == TimeRange::Day) {
            return array_merge(
                $this->getByFiveDays($user, $stores),
                $this->getWeekSummary($user, $stores),
                $this->getMonthSummary($user, $stores)
            );
        }

        if ($timeRange == TimeRange::Month) {
            return $this->getMonthByDay($user, $stores);
        }

        date_default_timezone_set('UTC');

        return [];
    }

	/**
	 * @param User       $user
	 * @param array|null $stores
	 *
	 * @return array
	 */
	private function getByFiveDays(User $user, ?array $stores): array
    {
        $dateEnd = new DateTime('today 23:59');
        $dateStart = new DateTime('today 00:00');
        $dateStart->sub(new DateInterval('P5D'));

        $period = new DatePeriod(
            $dateStart,
            new DateInterval('P1D'),
            $dateEnd
        );

        foreach ($period as $value) {
            $summary[] = [
                'date' => $value->format(DATE_ATOM),
                'sum' => '0',
                'transactionCount' => '0',
                'invoiceCount' => '0',
                'paidInvoiceCount' => '0',
            ];
        }

        $transactions = $this->getTransactionData($user, $dateStart, $stores);

        foreach ($transactions as $transaction) {
            foreach ($summary as $key => $value) {
                if ($value['date'] == $transaction->created_at->format(DATE_ATOM)) {
                    $summary[$key] = [
                        'date' => $transaction->created_at->format(DATE_ATOM),
                        'sum' => $transaction->sum,
                        'transactionCount' => (string)$transaction->count,
                    ];
                }
            }
        }

        $invoices = $this->getInvoiceData($user, $dateStart, $stores);

        foreach ($invoices as $invoice) {
            foreach ($summary as $key => $value) {
                if ($value['date'] == $invoice->created_at->format(DATE_ATOM)) {
                    $summary[$key]['invoiceCount'] = (string)($invoice->invoiceCount ?? 0);
                    $summary[$key]['paidInvoiceCount'] = (string)($invoice->paidInvoiceCount ?? 0);
                }
            }
        }

        return $summary;
    }

	/**
	 * @param User       $user
	 * @param array|null $stores
	 *
	 * @return array
	 */
	private function getWeekSummary(User $user, ?array $stores): array
    {
        $dateStart = new DateTime('today 23:59');
        $dateStart->sub(new DateInterval('P1W'));

        $transactionsQuery = Transaction::selectRaw(
            'SUM(transactions.amount_usd) as sum, COUNT(transactions.id) as count'
        )
            ->join('invoices', 'invoices.id', 'transactions.invoice_id')
            ->join('stores', 'stores.id', 'invoices.store_id')
            ->where([
                ['stores.user_id', $user->id],
                ['transactions.created_at', '>=', $dateStart],
                ['transactions.type', TransactionType::Invoice],
            ]);

        if ($stores) {
            $transactionsQuery->whereIn('stores.id', $stores);
        }

        $transaction = $transactionsQuery->first();

        $invoicesQuery = Invoice::selectRaw("
            SUM(CASE
                WHEN invoices.status = 'paid' or invoices.status = 'success'
                   THEN 1
                   ELSE 0
                END) as paidInvoiceCount,
            COUNT(invoices.id) as invoiceCount
        ")
            ->join('stores', 'stores.id', 'invoices.store_id')
	        ->join('transactions', 'transactions.invoice_id', 'invoices.id')
            ->where([
                ['stores.user_id', $user->id],
                ['transactions.created_at', '>=', $dateStart],
            ]);

        if ($stores) {
            $invoicesQuery->whereIn('stores.id', $stores);
        }

        $invoice = $invoicesQuery->first();

        $summary[] = [
            'date' => $dateStart->format(DATE_ATOM),
            'sum' => $transaction->sum ?? '0',
            'transactionCount' => (string)($transaction->count ?? 0),
            'invoiceCount' => (string)($invoice->invoiceCount ?? 0),
            'paidInvoiceCount' => (string)($invoice->paidInvoiceCount ?? 0),
        ];

        return $summary;
    }

	/**
	 * @param User       $user
	 * @param array|null $stores
	 *
	 * @return array
	 */
	private function getMonthSummary(User $user, ?array $stores): array
    {
        $dateStart = new DateTime('today 23:59');
        $dateStart->sub(new DateInterval('P1M'));

        $transactionsQuery = Transaction::selectRaw(
            'SUM(transactions.amount_usd) as sum, COUNT(transactions.id) as count'
        )
            ->join('invoices', 'invoices.id', 'transactions.invoice_id')
            ->join('stores', 'stores.id', 'invoices.store_id')
            ->where([
                ['stores.user_id', $user->id],
                ['transactions.created_at', '>=', $dateStart],
                ['transactions.type', TransactionType::Invoice],
            ]);

        if ($stores) {
            $transactionsQuery->whereIn('stores.id', $stores);
        }

        $transaction = $transactionsQuery->first();

        $invoicesQuery = Invoice::selectRaw("
            SUM(CASE
                WHEN invoices.status = 'paid' or invoices.status = 'success'
                   THEN 1
                   ELSE 0
                END) as paidInvoiceCount,
            COUNT(invoices.id) as invoiceCount
        ")
            ->join('stores', 'stores.id', 'invoices.store_id')
	        ->join('transactions', 'transactions.invoice_id', 'invoices.id')
            ->where([
                ['stores.user_id', $user->id],
                ['transactions.created_at', '>=', $dateStart],
            ]);

        if ($stores) {
            $invoicesQuery->whereIn('stores.id', $stores);
        }

        $invoice = $invoicesQuery->first();

        $summary[] = [
            'date' => $dateStart->format(DATE_ATOM),
            'sum' => $transaction->sum ?? '0',
            'transactionCount' => (string)($transaction->count ?? 0),
            'invoiceCount' => (string)($invoice->invoiceCount ?? 0),
            'paidInvoiceCount' => (string)($invoice->paidInvoiceCount ?? 0),
        ];

        return $summary;
    }

	/**
	 * @param User       $user
	 * @param array|null $stores
	 *
	 * @return array
	 */
	private function getMonthByDay(User $user, ?array $stores): array
    {
        $dateEnd = new DateTime('today 23:59');
        $dateStart = new DateTime('today 00:00');
        $dateStart->sub(new DateInterval('P1M'));

        $period = new DatePeriod(
            $dateStart,
            new DateInterval('P1D'),
            $dateEnd
        );

        foreach ($period as $value) {
            $summary[] = [
                'date' => $value->format(DATE_ATOM),
                'sum' => '0',
                'transactionCount' => '0',
                'invoiceCount' => '0',
                'paidInvoiceCount' => '0',
            ];
        }

        $transactions = $this->getTransactionData($user, $dateStart, $stores);

        foreach ($transactions as $transaction) {
            foreach ($summary as $key => $value) {
                if ($value['date'] == $transaction->created_at->format(DATE_ATOM)) {
                    $summary[$key] = [
                        'date' => $transaction->created_at->format(DATE_ATOM),
                        'sum' => $transaction->sum,
                        'transactionCount' => (string)$transaction->count,
                    ];
                }
            }
        }

        $invoices = $this->getInvoiceData($user, $dateStart, $stores);

        foreach ($invoices as $invoice) {
            foreach ($summary as $key => $value) {
                if ($value['date'] == $invoice->created_at->format(DATE_ATOM)) {
                    $summary[$key]['invoiceCount'] = (string)($invoice->invoiceCount ?? 0);
                    $summary[$key]['paidInvoiceCount'] = (string)($invoice->paidInvoiceCount ?? 0);
                }
            }
        }

        return $summary;
    }

	/**
	 * @param User       $user
	 * @param DateTime   $dateStart
	 * @param array|null $stores
	 *
	 * @return \App\Models\Model[]|Transaction[]|\LaravelIdea\Helper\App\Models\_IH_Model_C|\LaravelIdea\Helper\App\Models\_IH_Transaction_C
	 */
	private function getTransactionData(User $user, DateTime $dateStart, ?array $stores)
    {
        $timezone = $user->location ?? '+00:00';

        $transactionsQuery = Transaction::selectRaw(
            "DATE(CONVERT_TZ(transactions.created_at, '+00:00', '$timezone')) as created_at,
            SUM(transactions.amount_usd) as sum,
            COUNT(transactions.id) as count"
        )
            ->join('invoices', 'invoices.id',  '=', 'transactions.invoice_id')
            ->join('stores', 'stores.id',  '=', 'invoices.store_id')
            ->where([
                ['stores.user_id', $user->id],
                ['transactions.type', TransactionType::Invoice],
            ])
            ->whereRaw("DATE(CONVERT_TZ(transactions.created_at, '+00:00', '$timezone')) >= {$dateStart->format('Y-m-d')}");

        if ($stores) {
            $transactionsQuery->whereIn('stores.id', $stores);
        }

        return $transactionsQuery
            ->groupBy('created_at')
            ->get();
    }

	/**
	 * @param User       $user
	 * @param DateTime   $dateStart
	 * @param array|null $stores
	 *
	 * @return Invoice[]|\App\Models\Model[]|\LaravelIdea\Helper\App\Models\_IH_Invoice_C|\LaravelIdea\Helper\App\Models\_IH_Model_C
	 */
	private function getInvoiceData(User $user, DateTime $dateStart, ?array $stores)
    {
        $timezone = $user->location ?? '+00:00';

        $invoicesQuery = Invoice::selectRaw(
            "SUM(CASE
                WHEN invoices.status = 'paid' or invoices.status = 'success'
                   THEN 1
                   ELSE 0
                END) as paidInvoiceCount,
                DATE(CONVERT_TZ(transactions.created_at, '+00:00', '$timezone')) as created_at,
                COUNT(invoices.id) as invoiceCount"
        )
            ->join('stores', 'stores.id', '=', 'invoices.store_id')
	        ->join('transactions', 'transactions.invoice_id',  '=', 'invoices.id')
            ->where('stores.user_id', $user->id)
            ->whereRaw("DATE(CONVERT_TZ(transactions.created_at, '+00:00', '$timezone')) >= {$dateStart->format('Y-m-d')}");

        if ($stores) {
            $invoicesQuery->whereIn('stores.id', $stores);
        }

        return $invoicesQuery
            ->groupBy('created_at')
            ->get();
    }
}