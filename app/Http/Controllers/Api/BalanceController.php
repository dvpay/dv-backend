<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Balance\GetBalancesRequest;
use App\Http\Resources\Balance\GetAllBalancesCollection;
use App\Services\Balance\BalanceService;
use Psr\SimpleCache\InvalidArgumentException;
use OpenApi\Attributes as OA;

/**
 * BalanceController
 */
class BalanceController
{
    /**
     * @param BalanceService $balanceService
     */
    public function __construct(private readonly BalanceService $balanceService)
    {
    }

    /**
     * @param GetBalancesRequest $request
     *
     * @return GetAllBalancesCollection
     * @throws InvalidArgumentException
     */
    #[OA\Post(
        path: '/stores/balances',
        summary: 'Get user balances',
        security: [["bearerAuth" => []]],
        tags: ['Store'],
        responses: [
            new OA\Response(response: 200, description: "Get user balances", content: new OA\JsonContent(
                example: '{"result":{"totals":{"amountUsd":"4451.95807762","addressCount":{"total":0,"free":0,"busy":0,"hold":0}},"balances":[{"currency":"BTC.Bitcoin","balance":"0.02125022","balanceUsd":"915.95775862","addressCount":{"total":0,"free":0,"busy":0,"hold":0}},{"currency":"USDT.Tron","balance":"3536.000319","balanceUsd":"3536.00031900","addressCount":{"total":0,"free":0,"busy":0,"hold":0}}]},"errors":[]}',
            )),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(
                example: '{"errors":["You don\'t have permission to this action!"],"result":[]}'
            )),
        ],

    )]
    public function getAllBalances(GetBalancesRequest $request)
    {
        $user = $request->user();

        $balances = $this->balanceService->getAllBalanceFromProcessing($user);

        return new GetAllBalancesCollection($balances);
    }
}
