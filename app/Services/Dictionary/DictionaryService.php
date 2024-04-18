<?php

declare(strict_types=1);

namespace App\Services\Dictionary;

use App\Enums\ExchangeChainType;
use App\Enums\InvoiceStatus;
use App\Enums\RootSetting;
use App\Enums\UserRole;
use App\Enums\WebhookType;
use App\Enums\WithdrawalInterval;
use App\Facades\Settings;
use App\Models\Currency;
use App\Models\Exchange;
use App\Models\ExchangeDictionary;
use App\Models\ExchangeKey;
use App\Repositories\RateSourceRepository;
use App\Services\Processing\Contracts\HeartbeatContract;
use JetBrains\PhpStorm\Deprecated;

class DictionaryService
{
    public function __construct(
        private readonly RateSourceRepository $rateSourceRepository,
        private readonly HeartbeatContract    $processingService
    )
    {
    }

    /**
     * @throws \Exception
     */
    public function dictionaries(): array
    {
        return [
            'currencies'          => $this->getCurrencies(),
            'rateSources'         => $this->getRateSources(),
            'webhookTypes'        => $this->getWebhookTypes(),
            'blockchains'         => $this->getBlockchains(),
            'invoiceStatuses'     => $this->getInvoiceStatuses(),
            'withdrawalIntervals' => $this->getWithdrawalIntervals(),
            'locations'           => $this->getLocations(),
            'exchanges'           => $this->getExchanges(),
            'exchangeCurrencies'  => $this->getExchangeCurrencies(),
            'exchangesKeyTypes'   => $this->getExchangeKeyTypes(),
            'api'                 => $this->getApiDocumentation(),
            'roles'               => UserRole::values(),
            'chain'               => ExchangeChainType::cases(),
            'registrationEnable'  => Settings::get(RootSetting::RegistrationEnable->value),
            // #[Deprecated]
            'version'             => config('app.version'),
            // #[Deprecated]
            'processingVersion'   => $this->processingVersion(),
            'versions' => [
                'backend' => [
                    #TODO: Create versioning mechanism
                    'tag' => config('app.version'),
                    'commitHash' => null,
                    ],
                'processing' => [
                    'tag' => $this->processingVersion()?->release ?? null,
                    'commitHash' => $this->processingVersion()?->commitHash ?? null,
                ],
            ]
        ];
    }

    private function getCurrencies(): ?array
    {
        return Currency::select(
            'id', 'code', 'name', 'precision', 'is_fiat as isFiat', 'contract_address as contractAddress'
        )->get()->toArray();
    }

    private function getRateSources(): ?array
    {
        $rateSources = $this->rateSourceRepository->getActualRateSources();

        $result = [];
        foreach ($rateSources as $rateSource) {
            $result[] = $rateSource->name->value;
        }

        return $result;
    }

    private function getWebhookTypes(): ?array
    {
        $enums = WebhookType::cases();

        $result = [];
        foreach ($enums as $enum) {
            $result[$enum->value] = $enum->title();
        }

        return $result;
    }

    private function getBlockchains(): array
    {
        $result = [];

        $currencies = Currency::where('is_fiat', false)->get();
        foreach ($currencies as $currency) {

            $listCurrencies = [];
            foreach ($currencies as $currencyCode) {
                if ($currency->blockchain == $currencyCode->blockchain) {
                    $listCurrencies[] = $currencyCode->code;
                }
            }

            $result[$currency->blockchain->value] = [
                'name'          => $currency->blockchain->value,
                'title'         => $currency->blockchain->name,
                'nativeToken'   => $currency->blockchain->getNativeToken(),
                'importMethods' => [
                    'address',
                    'mnemonic',
                ],
                'currencies'    => $listCurrencies,
            ];
        }

        return $result;
    }

    private function getInvoiceStatuses(): array
    {
        $result = [];
        $statuses = InvoiceStatus::cases();
        foreach ($statuses as $status) {
            $result[] = $status->title();
        }

        return $result;
    }

    private function getWithdrawalIntervals(): array
    {
        return array_map(fn(WithdrawalInterval $interval) => $interval->getName(), WithdrawalInterval::getValue());
    }


    private function getLocations(): array
    {
        return timezone_identifiers_list();
    }

    private function getExchanges(): array
    {
        $exchanges = Exchange::where('is_active', true)->get();

        $result = [];
        foreach ($exchanges as $exchange) {
            $result[] = [
                'name' => $exchange->name,
                'slug' => $exchange->slug,
            ];
        }

        return $result;
    }

    private function getExchangeCurrencies(): array
    {
        $exchanges = Exchange::where('is_active', true)->get();

        $result = [];
        foreach ($exchanges as $exchange) {
            $currencies = ExchangeDictionary::select('from_currency_id as fromCurrencyId', 'to_currency_id as toCurrencyId')
                ->where('exchange_id', $exchange->id)
                ->get();

            $pairs = [];
            foreach ($currencies as $currency) {
                $pairs[$currency->fromCurrencyId][] = $currency->toCurrencyId;
            }

            $result[] = [
                'exchange'   => $exchange->name,
                'slug'       => $exchange->slug,
                'currencies' => $pairs,
            ];
        }

        return $result;
    }

    private function getExchangeKeyTypes(): array
    {
        $result = [];

        $exchanges = Exchange::where('is_active', true)->get();
        foreach ($exchanges as $exchange) {
            $keyTypes = ExchangeKey::where('exchange_id', $exchange->id)->get();
            $keys['exchange'] = $exchange->name;
            $keys['keys'] = [];
            foreach ($keyTypes as $keyType) {
                $keys['keys'][] = $keyType->key;
            }

            $result[] = $keys;
        }

        return $result;
    }

    private function getApiDocumentation(): array
    {
        return [
            'documentationUrl' => route('l5-swagger.default.api'),
        ];
    }

    /**
     * @throws \Exception
     */
    public function processingVersion()
    {
        $status = $this->processingService->getStatusService();
        $status = json_decode($status->getBody()->getContents());

        return $status->appVersion ?? 'unknown';
    }
}
