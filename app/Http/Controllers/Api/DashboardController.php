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
use OpenApi\Attributes as OA;

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
    #[OA\Get(
        path: '/stores/dashboard/deposit/transactions',
        summary: 'Get deposit transactions',
        security: [["bearerAuth" => []]],
        tags: ['Dashboard'],
        responses: [
            new OA\Response(response: 200, description: "Get static address", content: new OA\JsonContent(
                example: '{"result":[{"date":"2024-02-13T16:12:20+00:00","invoiceId":"50db9764-5e7a-4aa9-9973-b73f23d2684d","custom":null,"description":null,"storeName":"Store1","amountUsd":"115.06","amount":"0.00232990","tx":"7afe4a0c353a24e896cb757cfca222fd89d86541d10b80244c1ce1d1581ab07e","explorerLink":"https:\/\/www.blockchain.com\/btc\/tx\/7afe4a0c353a24e896cb757cfca179fd89d86333d10b80244c1ce1d1581ab07e","currencyId":"BTC.Bitcoin"},{"date":"2024-02-13T16:08:20+00:00","invoiceId":"4a17e584-a9a7-498a-94e9-1ecd3463c09b","custom":null,"description":null,"storeName":"Store2","amountUsd":"15.00","amount":"15.00000000","tx":"03b9c47f0b28b51cb6800c7778074c119aeee5fea864cda65901a6b22f2296e7","explorerLink":"https:\/\/tronscan.org\/#\/transaction\/03b9c47f0b28b51cb6800c7733334c959aeee5fea864cda65901a6b22f2296e7","currencyId":"USDT.Tron"}],"errors":[]}',
            )),
        ],

    )]
    public function getDepositTransactions(GetDepositTransactionsRequest $request): GetDepositTransactionCollection
    {
        $user = $request->user();
        $stores = $request->input('stores') ?? null;
        $timeRange = TimeRange::tryFrom($request->input('range'));

        $transactions = $this->dashboardService->getDepositTransactions($user, $stores, $timeRange);

        return new GetDepositTransactionCollection($transactions);
    }
}