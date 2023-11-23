<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Balance\GetBalancesRequest;
use App\Http\Resources\Balance\GetAllBalancesCollection;
use App\Services\Balance\BalanceService;
use Psr\SimpleCache\InvalidArgumentException;

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
    public function getAllBalances(GetBalancesRequest $request)
    {
        $user = $request->user();

        $balances = $this->balanceService->getAllBalanceFromProcessing($user);

        return new GetAllBalancesCollection($balances);
    }
}
