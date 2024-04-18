<?php

declare(strict_types=1);

namespace App\Enums;

enum Queue: string
{
    case Notifications = 'notifications';
    case Monitor = 'monitor';
    case Transfer = 'transfer';
    case StoreWebHook = 'store-webhook';
    case StoreWebHookRetry = 'store-webhook-retry';
    case Default = 'default';


}
