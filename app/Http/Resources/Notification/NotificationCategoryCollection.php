<?php

namespace App\Http\Resources\Notification;

use App\Http\Resources\BaseCollection;
/** @see \App\Models\Notification\NotificationCategory */
class NotificationCategoryCollection extends BaseCollection
{
    public $collects =  NotificationCategoryResource::class;
}
