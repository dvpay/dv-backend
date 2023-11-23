<?php

declare(strict_types=1);

namespace App\Dto\Webhook;

use App\Dto\ArrayDto;
use App\Enums\WebhookType;

class TestWebhookDto extends ArrayDto
{
    public readonly WebhookType $webhookType;
    public readonly string $orderId;
}
