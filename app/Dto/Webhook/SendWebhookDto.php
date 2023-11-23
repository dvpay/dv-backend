<?php

declare(strict_types=1);

namespace App\Dto\Webhook;

use App\Dto\ArrayDto;
use App\Enums\HttpMethod;

class SendWebhookDto extends ArrayDto
{
    public readonly HttpMethod $method;
    public readonly string $uri;
    public readonly array $data;
    public readonly string $secret;
}
