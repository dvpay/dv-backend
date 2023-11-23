<?php

declare(strict_types=1);

namespace App\Http\Resources\Webhook;

use App\Http\Resources\BaseCollection;

class WebhookCollection extends BaseCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = WebhookResource::class;
}