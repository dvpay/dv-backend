<?php

declare(strict_types=1);

namespace App\Http\Resources\Processing;

use App\Http\Resources\BaseCollection;

class ProcessingWalletCollection extends BaseCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ProcessingWalletResource::class;
}
