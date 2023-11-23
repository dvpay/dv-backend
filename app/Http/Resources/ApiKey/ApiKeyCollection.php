<?php

declare(strict_types=1);

namespace App\Http\Resources\ApiKey;

use App\Http\Resources\BaseCollection;

class ApiKeyCollection extends BaseCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ApiKeyResource::class;
}