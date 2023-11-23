<?php

declare(strict_types=1);

namespace App\Services\RateSource;

use App\Enums\HeartbeatServiceName;
use App\Enums\HeartbeatStatus;
use App\Enums\RateSource;
use App\Exceptions\ProcessingException;
use App\Interfaces\RateSource as RateSourceInterface;
use App\Jobs\HeartbeatStatusJob;
use App\Models\Service;
use App\Models\ServiceLogLaunch;
use App\Services\Currency\CurrencyRateCheck;
use App\Services\Currency\CurrencyStore;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Binance
 */
class Binance implements RateSourceInterface
{
    /**
     * @var ServiceLogLaunch
     */
    private ServiceLogLaunch $serviceLogLaunch;
    /**
     * @var Service
     */
    private Service $service;

    /**
     * @param Client $guzzle
     * @param CurrencyRateCheck $currencyRateCheck
     * @param CurrencyStore $currencyStore
     */
    public function __construct(
        private readonly Client            $guzzle,
        private readonly CurrencyRateCheck $currencyRateCheck,
        private readonly CurrencyStore     $currencyStore
    )
    {

    }

    /**
     * @param string $uri
     * @param array $currencies
     * @return void
     * @throws InvalidArgumentException|GuzzleException|Throwable
     */
    public function loadCurrencyPairs(string $uri, array $currencies): void
    {
        $currencyPairs = $this->getPairs($uri);

        foreach ($currencies as $currency) {
            foreach ($currencyPairs as $pair) {
                if ($currency['to']->value . $currency['from']->value != $pair->symbol) {
                    continue;
                }

                $rate = number_format((float)$pair->price, 8, '.', '');

                $this->currencyRateCheck->checkRate(RateSource::Binance, $currency['from'], $currency['to'], $rate);

                $this->currencyStore->set(RateSource::Binance, $currency['from'], $currency['to'], $rate);
            }
        }
    }

    /**
     * @param string $uri
     * @return mixed
     * @throws GuzzleException|Throwable
     */
    private function getPairs(string $uri): mixed
    {
        $this->initMonitor();
        HeartbeatStatusJob::dispatch(
            service: $this->service,
            status: HeartbeatStatus::InProgress,
            message: 'Start get pairs',
            serviceLogLaunch: $this->serviceLogLaunch,
        );
        try {
            $response = $this->guzzle->get($uri);

            if ($response->getStatusCode() !== Response::HTTP_OK) {
                throw new Exception(__('Binance service is not responding'));
            }

            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::Up,
                message: 'The command was successful!',
                serviceLogLaunch: $this->serviceLogLaunch,
            );

            return json_decode($response->getBody()->getContents());
        } catch (Throwable $e) {
            HeartbeatStatusJob::dispatch(
                service: $this->service,
                status: HeartbeatStatus::Down,
                message: 'Service is down. Reason: :reasonText.',
                messageVariable: ['reasonText' => $e->getMessage()],
                serviceLogLaunch: $this->serviceLogLaunch,
            );

            throw $e;
        }
    }

    protected function initMonitor(): void
    {
        $this->service = Service::where('slug', HeartbeatServiceName::ServiceBinance)
            ->first();

        $this->serviceLogLaunch = ServiceLogLaunch::create([
            'service_id' => $this->service->id,
            'status'     => HeartbeatStatus::InProgress
        ]);
    }
}