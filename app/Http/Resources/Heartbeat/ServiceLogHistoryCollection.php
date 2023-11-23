<?php

declare(strict_types=1);

namespace App\Http\Resources\Heartbeat;

use App\Http\Resources\BaseCollection;
use App\Models\Service;

/**
 * ServiceLogHistoryCollection
 */
class ServiceLogHistoryCollection extends BaseCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = ServiceLogHistoryResource::class;
}
