<?php

namespace App\Http\Resources\Notification;

use App\Http\Resources\BaseCollection;

/** @see \App\Models\Notification\NotificationTarget */
class NotificationTargetCollection extends BaseCollection
{
    public $collects = NotificationTargetResource::class;
}
