<?php

declare(strict_types=1);

namespace App\Http\Resources\Balance;

use App\Http\Resources\BaseCollection;
use Illuminate\Http\Request;

/**
 * GetAllBalancesCollection
 */
class GetAllBalancesCollection extends BaseCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = GetAllBalancesResource::class;

    /**
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'totals'   => [
                'amountUsd' => (string)$this->collection->sum('balanceUsd'),
            ],
            'balances' => $this->collection,
        ];
    }
}
