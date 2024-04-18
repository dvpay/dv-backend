<?php

namespace App\WebhookServer;

use App\Events\PaymentReceivedEvent;
use App\Enums\Metric;
use App\Events\UnconfirmedTransactionCreatedEvent;
use App\Facades\Prometheus;
use App\WebhookServer\Events\WebhookCallFailedEvent;
use App\WebhookServer\Events\WebhookCallSucceededEvent;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\TransferStats;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

class CallWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ?string $webhookUrl = null;

    public string $httpVerb;

    public int $tries;
    public int $requestTimeout;

    public array $headers = [];

    public array|string $payload = [];

    public array $meta = [];

    public array $tags = [];
    public int $attempts = 1;

    public string $uuid = '';
    public string $outputType = "JSON";

    protected ?ResponseInterface $response = null;

    protected ?string $errorType = null;

    protected ?string $errorMessage = null;

    protected ?TransferStats $transferStats = null;

    public function handle(): void
    {
        $lastAttempt = $this->attempts >= $this->tries;

        try {
            $body = strtoupper($this->httpVerb) === 'GET'
                ? ['query' => $this->payload]
                : ['body' => $this->generateBody()];

            $this->response = $this->createRequest($body);

            if (!Str::startsWith($this->response->getStatusCode(), 2)) {
                throw new Exception('Webhook call failed');
            }

            $this->dispatchEvent(WebhookCallSucceededEvent::class);
            return;
        } catch (Exception $exception) {
            if ($exception instanceof RequestException) {
                $this->response = $exception->getResponse();
                $this->errorType = get_class($exception);
                $this->errorMessage = $exception->getMessage();
            }

            if ($exception instanceof ConnectException) {
                $this->errorType = get_class($exception);
                $this->errorMessage = $exception->getMessage();
            }

            if (!$lastAttempt) {
                $delay = 60 * pow(2, $this->attempts);
                match ($this->meta['eventType']) {
                    'UnconfirmedTransaction' => event(new UnconfirmedTransactionCreatedEvent($this->meta['transaction']->refresh(), $delay, $this->attempts + 1)),
                    'PaymentReceived' => event(new PaymentReceivedEvent($this->meta['transaction']->refresh(), $delay, $this->attempts + 1)),
                };
            }

            $this->dispatchEvent(WebhookCallFailedEvent::class);

        }
    }

    protected function getClient(): ClientInterface
    {
        return app(Client::class);
    }

    /**
     * @throws GuzzleException
     */
    protected function createRequest(array $body): ResponseInterface
    {
        $client = $this->getClient();


        return $client->request($this->httpVerb, $this->webhookUrl, array_merge(
            [
                'timeout'  => $this->requestTimeout,
                'headers'  => $this->headers,
                'on_stats' => function (TransferStats $stats) {
                    $this->transferStats = $stats;

                    $service = parse_url($this->webhookUrl, PHP_URL_HOST);
                    $status = $stats->getResponse()?->getStatusCode() ?? 'unknown';
                    $method = $stats->getRequest()->getMethod() ?? 'unknown';
                    Prometheus::histogramObserve(
                        Metric::CommonExternalHttpClientStats->getName(),
                        $stats->getTransferTime(),
                        [$service, $method, $status]
                    );
                },
            ],
            $body,
        ));
    }

    private function generateBody(): string
    {
        return match ($this->outputType) {
            "RAW" => $this->payload,
            default => json_encode($this->payload),
        };
    }

    public function tags(): array
    {
        return $this->tags;
    }


    protected function shouldBeRemovedFromQueue(): bool
    {
        return false;
    }

    private function dispatchEvent(string $eventClass): void
    {
        event(new $eventClass(
            $this->httpVerb,
            $this->webhookUrl,
            $this->payload,
            $this->headers,
            $this->meta,
            $this->tags,
            $this->attempts(),
            $this->response,
            $this->errorType,
            $this->errorMessage,
            $this->uuid,
            $this->transferStats
        ));
    }
}
