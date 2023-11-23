<?php

declare(strict_types=1);

namespace App\Http\Resources\Withdrawal;

use App\Http\Resources\BaseCollection;

class WithdrawalListCollection extends BaseCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = WithdrawalListResource::class;
}