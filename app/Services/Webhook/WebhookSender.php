<?php

declare(strict_types=1);

namespace App\Services\Webhook;

use App\Dto\Webhook\SendWebhookDto;
use App\Enums\HttpMethod;
use App\Exceptions\WebhookException;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class WebhookSender
{
    public function __construct(
        private readonly Client $client,
        private readonly string $timeout
    )
    {
    }

    public function send(SendWebhookDto $dto): ResponseInterface
    {
        $args = [
            RequestOptions::HTTP_ERRORS => false,
            RequestOptions::TIMEOUT => $this->timeout,
            RequestOptions::HEADERS => [
                'X-Sign' => hash('sha256', json_encode($dto->data) . $dto->secret),
            ],
        ];

        if ($dto->method === HttpMethod::GET) {
            $args[RequestOptions::QUERY] = $dto->data;
        } else {
            $args[RequestOptions::JSON] = $dto->data;
        }

        try {
            Log::error('[DTO]', (array)$dto);
            return $this->client->request($dto->method->value, $dto->uri, $args);
        } catch (Throwable $e) {
            throw new WebhookException(__("Can't send webhook: ") . $e->getMessage());
        }
    }
}
