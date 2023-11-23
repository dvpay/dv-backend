<?php

namespace App\Http\Resources\Processing;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class ProcessingWalletStatsResource extends BaseResource
{
    public function toArray(Request $request): array
    {
        return [
            'date'           => $this['date'],
            'count'          => $this['count'],
            'totalEnergy'    => $this['total_energy'],
            'totalBandwidth' => $this['total_bandwidth']
        ];
    }
}
