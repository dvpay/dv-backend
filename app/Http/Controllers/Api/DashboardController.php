<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\TimeRange;
use App\Http\Requests\Dashboard\GetDepositSummaryRequest;
use App\Http\Requests\Dashboard\GetDepositTransactionsRequest;
use App\Http\Resources\Dashboard\GetDepositTransactionCollection;
use App\Http\Resources\DefaultResponseResource;
use App\Services\Dashboard\DashboardService;
use App\Services\Dashboard\DepositSummaryService;
use Illuminate\Contracts\Auth\Authenticatable;

/**
 * DashboardController
 */
class DashboardController extends ApiController
{
    /**
     * @param DashboardService $dashboardService
     * @param DepositSummaryService $depositSummaryService
     */
    public function __construct(
        private readonly DashboardService      $dashboardService,
        private readonly DepositSummaryService $depositSummaryService
    )
    {
    }

    /**
     * @param GetDepositSummaryRequest $request
     *
     * @return DefaultResponseResource
     */
    public function getDepositSummary(GetDepositSummaryRequest $request): DefaultResponseResource
    {
        $user = $request->user();
        $timeRange = TimeRange::tryFrom($request->input('range'));
        $stores = $request->input('stores');

        $summary = $this->depositSummaryService->getDepositSummary($user, $timeRange, $stores);

        return new DefaultResponseResource($summary);
    }

    /**
     * @param GetDepositTransactionsRequest $request
     *
     * @return GetDepositTransactionCollection
     */
    public function getDepositTransactions(GetDepositTransactionsRequest $request): GetDepositTransactionCollection
    {
        $user = $request->user();
        $stores = $request->input('stores') ?? null;
        $timeRange = TimeRange::tryFrom($request->input('range'));

        $transactions = $this->dashboardService->getDepositTransactions($user, $stores, $timeRange);

        return new GetDepositTransactionCollection($transactions);
    }

    /**
     * Calculates exchanged amount and saved on commission amount.
     *
     * GET /dashboard/economy
     *
     * @param Authenticatable $user
     * @return DefaultResponseResource
     */
    public function getEconomyStats(Authenticatable $user): DefaultResponseResource
    {
        $data = [
            'saved'     => $this->dashboardService->getSavedOnCommissionStats($user),
            'exchanged' => $this->dashboardService->getGetExchangedStats($user),
            'withdrawn' => $this->dashboardService->getExchangeColdWalletWithdrawalsStats($user),
        ];

        return new DefaultResponseResource($data);
    }
}
