<?php

declare(strict_types=1);

namespace App\Http\Resources\Dashboard;

use App\Http\Resources\BaseCollection;

class GetDepositTransactionCollection extends BaseCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = GetDepositTransactionResource::class;
}
