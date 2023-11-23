<?php

declare(strict_types=1);

namespace App\Http\Resources\Processing;

use App\Enums\Blockchain;
use App\Http\Resources\BaseResource;
use App\Models\Currency;
use Illuminate\Http\Request;

class ProcessingWalletResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $blockchain = Blockchain::tryFrom($this->blockchain);
        $currency = Currency::where('code', $blockchain->getNativeToken())->first();

        return [
            'blockchain'     => $this->blockchain,
            'address'        => $this->address,
            'balance'        => $this->balance,
            'minBalance'     => $currency->withdrawal_min_balance,
            'energyLimit'    => $this->energyLimit,
            'energy'         => $this->energy,
            'bandwidthLimit' => $this->bandwidthLimit,
            'bandwidth'      => $this->bandwidth
        ];
    }
}