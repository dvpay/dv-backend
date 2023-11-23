<?php

namespace App\Enums;

enum NotificationCategoryType: string
{
    case Errors = 'errors';
    case Events = 'events';
    case Reports = 'reports';

}