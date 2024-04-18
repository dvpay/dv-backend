<?php
declare(strict_types=1);

namespace App\Services\Processing;

use App\Console\Commands\Transfer;
use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Enums\HttpMethod;
use App\Enums\Metric;
use App\Exceptions\ProcessingException;
use App\Facades\Prometheus;
use App\Jobs\HeartbeatStatusJob;
use App\Services\Processing\Contracts\Client as ProcessingClient;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use function App\Helpers\array\nullValuesToEmptyString;

class HttpClient implements ProcessingClient
{
    public function __construct(
        private readonly Client $client,
        private readonly string $clientId,
        private readonly string $clientKey
    )
    {
    }

    public function request(HttpMethod $method, string $uri, array $data, ?string $statsUri = null): ResponseInterface
    {
        $statsUri = $statsUri ?? $uri;

        $args = [
            RequestOptions::HTTP_ERRORS => false,
        ];

        if ($method === HttpMethod::GET) {
            $args[RequestOptions::QUERY] = $data;
        } else {
            if (empty($data)) {
                $args[RequestOptions::BODY] = '{}';
            } else {
                $args[RequestOptions::JSON] = $data;
            }
        }

        if ($data) {
            $rawData = json_encode(nullValuesToEmptyString($data));
        } else {
            $rawData = "{}";
        }

        $sign = hash('sha256', $rawData . $this->clientKey);

        $args[RequestOptions::HEADERS] = [
            'Content-Type' => 'application/json',
            'X-Client-Id' => $this->clientId,
            'X-Sign' => $sign,
        ];

        $args[RequestOptions::ON_STATS] = function (TransferStats $stats) use ($statsUri) {
            $status = $stats->getResponse()?->getStatusCode() ?? 'unknown';
            $method = $stats->getRequest()->getMethod() ?? 'unknown';
            Prometheus::histogramObserve(
                Metric::ProcessingHttpClientStats->getName(),
                $stats->getTransferTime(),
                [$statsUri, $method, $status]
            );
        };

        try {
            $result = $this->client->request($method->value, $uri, $args);

            return $result;
        } catch (\Throwable $e) {

            throw new ProcessingException(__('Cannot do request to service'), previous: $e);
        }
    }
}