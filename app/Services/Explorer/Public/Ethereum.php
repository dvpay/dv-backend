<?php

namespace App\Services\Explorer\Public;

use App\Enums\Metric;
use App\Facades\Prometheus;
use App\Models\Currency;
use App\Services\Explorer\Public\Interface\ExplorerInterface;
use GuzzleHttp\TransferStats;
use Illuminate\Support\Facades\Http;

class Ethereum implements ExplorerInterface
{
    private const API_ENDPOINT = 'https://api.etherscan.io/api';

//$apiUrl = "https://api.etherscan.io/api?module=account&action=tokenbalance&contractaddress={$usdtContractAddress}&address={$ethereumAddress}&tag=latest&apikey={$apiKey}";
    public function getAddressBalance(string $address): string
    {
        $currency = Currency::where('id', '=', 'USDT.ETH')->first();

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
        ->get(self::API_ENDPOINT, [
            'module'          => 'account',
            'action'          => 'tokenbalance',
            'contractaddress' => $currency->contract_address,
            'address'         => $address,
            'apikey'          => config('explorer.public.ethereum.apiKey')
        ]);

        if ($response->successful()) {
            $usdtBalanceWei = $response->object()->result;
            return bcdiv($usdtBalanceWei, '1000000', 2);
        }
    }
}
