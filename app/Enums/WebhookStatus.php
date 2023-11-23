<?php

declare(strict_types=1);

namespace App\Enums;

enum WebhookStatus: string
{
    case Success = 'success';
    case Fail = 'fail';
}
