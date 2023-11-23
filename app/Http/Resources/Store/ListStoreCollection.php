<?php

declare(strict_types=1);

namespace App\Http\Resources\Store;

use App\Http\Resources\BaseCollection;

class ListStoreCollection extends BaseCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ListStoreResource::class;
}