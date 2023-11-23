<?php

declare(strict_types=1);

namespace App\Http\Resources\Balance;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;
use Exception;

/**
 * GetAllBalancesResource
 */
class GetAllBalancesResource extends BaseResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param Request $request
	 *
	 * @return array
	 * @throws Exception
	 */
	public function toArray($request): array
	{
		return [
			'currency'   => $this->resource['currency'],
			'balance'    => $this->resource['balance'],
			'balanceUsd' => $this->resource['balanceUsd'],
			'addressCount' => $this->resource['addressCount'],
		];
	}
}
