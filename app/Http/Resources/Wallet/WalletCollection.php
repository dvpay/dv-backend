<?php

declare(strict_types=1);

namespace App\Http\Resources\Wallet;

use App\Http\Resources\BaseCollection;

class WalletCollection extends BaseCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = WalletResource::class;
}