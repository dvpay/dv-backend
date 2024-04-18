<?php

namespace App\Services\Explorer\Public;

use App\Enums\Metric;
use App\Exceptions\ApiException;
use App\Facades\Prometheus;
use App\Services\Explorer\Public\Interface\ExplorerInterface;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\Http;

class Bitcoin implements ExplorerInterface
{
    private const API_ENDPOINT = 'https://api.bitaps.com/btc/v1/blockchain';

    public function getAddressBalance(string $address): string
    {
        try {
            $response = Http::connectTimeout(30)
                ->retry('3', 100)
                ->withOptions([
                    'proxy' => config('explorer.proxy'),
                    'on_stats' => function (TransferStats $stats) {
                        $service = parse_url(self::API_ENDPOINT, PHP_URL_HOST);
                        $status = $stats->getResponse()?->getStatusCode() ?? 'unknown';
                        $method = $stats->getRequest()->getMethod() ?? 'unknown';
                        Prometheus::histogramObserve(
                            Metric::CommonExternalHttpClientStats->getName(),
                            $stats->getTransferTime(),
                            [$service, $method, $status]
                        );
                    },
                ])
                ->get(self::API_ENDPOINT . '/address/state/' .$address);
        } catch (\Exception $e) {
            #TODO: Metric to unsuccessful request
            # And refactor this point
            return '0';

        }


        if (!$response->successful()) {
            throw new ApiException('Error');
        }

        $balance = $response->object()?->data?->balance;

        if(null === $balance) {
            throw new ApiException('Error');
        }

        return  $balance / 100000000;
    }

    public function getAddressInfo(string $address): ?object
    {
        $response = Http::connectTimeout(30)
            ->retry('3', 100)
            ->withOptions([
                'proxy' => config('explorer.proxy'),
                'on_stats' => function (TransferStats $stats) {
                    $service = self::API_ENDPOINT;
                    $status = $stats->getResponse()?->getStatusCode() ?? 'unknown';
                    $method = $stats->getRequest()->getMethod() ?? 'unknown';
                    Prometheus::histogramObserve(
                        Metric::ProcessingHttpClientStats->getName(),
                        $stats->getTransferTime(),
                        [$service, $method, $status]
                    );
                },
            ])
            ->throw()
            ->get(self::API_ENDPOINT . '/address/' .$address);

        if ($response->successful()) {
            return  $response->object();
        } else {
            throw new ApiException('Error');
        }
    }

    public function getAddressTransaction(string $address)
    {
        $response = Http::connectTimeout(30)
            ->retry('3', 100)
            ->withOptions([
                'proxy' => config('explorer.proxy'),
                'on_stats' => function (TransferStats $stats) {
                    $service = self::API_ENDPOINT;
                    $status = $stats->getResponse()?->getStatusCode() ?? 'unknown';
                    $method = $stats->getRequest()->getMethod() ?? 'unknown';
                    Prometheus::histogramObserve(
                        Metric::ProcessingHttpClientStats->getName(),
                        $stats->getTransferTime(),
                        [$service, $method, $status]
                    );
                },
            ])
            ->get(self::API_ENDPOINT . '/address/unconfirmed/transactions/' . $address);
        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['data'])) {
                return collect($data['data']['list']);
            }
        } else {
            throw new ApiException('Error');
        }
    }


}
