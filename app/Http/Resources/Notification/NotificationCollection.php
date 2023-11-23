<?php

namespace App\Http\Resources\Notification;

use App\Http\Resources\BaseCollection;
/** @see \App\Models\Notification\Notification */
class NotificationCollection extends BaseCollection
{
    public $collects = NotificationResource::class;
}
