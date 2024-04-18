<?php

namespace App\WebhookServer\Events;


class DispatchingWebhookCallEvent
{
    public function __construct(
        public string $httpVerb,
        public string $webhookUrl,
        public array|string $payload,
        public array $headers,
        public string $uuid,
    ) {
    }
}
