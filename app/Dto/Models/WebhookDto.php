<?php

declare(strict_types=1);

namespace App\Dto\Models;

use App\Dto\ArrayDto;

class WebhookDto extends ArrayDto
{
    public readonly string $storeId;
    public readonly string $url;
    public readonly string $secret;
    public readonly bool $enabled;
    public readonly array $events;
}