<?php

namespace App\Services\Explorer\Public;

use App\Enums\Metric;
use App\Facades\Prometheus;
use App\Models\Currency;
use App\Services\Explorer\Public\Interface\ExplorerInterface;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\Http;

class Tron implements ExplorerInterface
{
    private const API_ENDPOINT = 'https://api.trongrid.io/v1/accounts/';

    public function getAddressBalance(string $address): string
    {
        $response = Http::withOptions([
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
            ->get(self::API_ENDPOINT . $address);
        $currency = Currency::where('id', '=', 'USDT.Tron')->first();

        if ($response->successful()) {
            $data = $response->json();

            if (isset($data['data'][0]['trc20'])) {
                $balances = collect($data['data'][0]['trc20']);

                $usdtBalance = $balances->first(function ($item) use ($currency) {
                    return key($item) === $currency->contract_address;
                });

                if ($usdtBalance) {
                    $balanceValue = $usdtBalance[$currency->contract_address];
                    return $balanceValue > 0 ? $balanceValue / 1000000 : 0;
                }
                return 'Not have balance USDT trc20 token';
            } else {
                return 'Unable to retrieve balances.';
            }
        } else {
            return 'Error: ' . $response->status();
        }
    }
}
