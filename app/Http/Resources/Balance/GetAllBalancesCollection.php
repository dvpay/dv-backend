<?php

declare(strict_types=1);

namespace App\Http\Resources\Balance;

use App\Enums\InvoiceAddressState;
use App\Http\Resources\BaseCollection;

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
	 * @param $request
	 *
	 * @return array
	 */
	public function toArray($request): array
	{
		$collection = $this->collection;
		$amountUsd  = $collection->sum('balanceUsd');

        $addressCount = ['total' => $collection->sum('addressCount.total')];
        foreach (InvoiceAddressState::cases() as $state) {
            $addressCount[$state->value] = $collection->sum('addressCount.' . $state->value);
        }

        return [
            'totals'   => [
                'amountUsd' => (string)$amountUsd,
                'addressCount' => $addressCount,
            ],
            'balances' => $collection,
        ];
	}
}
