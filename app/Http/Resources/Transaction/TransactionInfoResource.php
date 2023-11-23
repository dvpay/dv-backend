<?php

declare(strict_types=1);

namespace App\Http\Resources\Transaction;

use App\Enums\CurrencySymbol;
use App\Enums\InvoiceStatus;
use App\Enums\RateSource;
use App\Enums\TransactionStatus;
use App\Http\Resources\BaseResource;
use App\Http\Resources\Payer\PayerExternalResource;
use App\Http\Resources\Payer\PayerResource;
use App\Services\Currency\CurrencyConversion;
use App\Services\Currency\CurrencyRateService;

class TransactionInfoResource extends BaseResource
{
    public function toArray($request): array
    {
        if (isset($this->resource->transaction->rate)) {
            $result = $this->getInternalResponse();
        } else {
            $result = $this->getProcessingResponse();
        }

        return $result;
    }

    private function getInternalResponse(): array
    {
        $store = $this->resource->invoice->store;

        $result = [
            'txId' => $this->resource->transaction->tx_id,
            'currency' => $this->resource->currency->code,
            'blockchain' => $this->resource->currency->blockchain,
            'contractAddress' => $this->resource->currency->contract_address,
            'amount' => $this->resource->transaction->amount,
            'amountUsd' => $this->resource->transaction->amount_usd,
            'rate' => $this->resource->transaction->rate,
            'time' => $this->resource->transaction->network_created_at,
            'sender' => $this->resource->transaction->from_address,
            'receiver' => $this->resource->transaction->to_address,
            'status' => TransactionStatus::Processed->value,
            'relatedInvoices' => [
                'id' => $this->resource->invoice->id,
                'orderId' => $this->resource->invoice->order_id,
                'store' => [
                    'id' => $store->id,
                    'title' => $store->name,
                ],
                'createdAt' => $this->resource->invoice->created_at,
                'expiredAt' => $this->resource->invoice->expired_at,
                'amount' => $this->resource->invoice->amount,
                'status' => $this->resource->invoice->status->value,
            ],
        ];

        if ($this->resource->invoice->status !== InvoiceStatus::Waiting) {
            foreach ($this->resource->webhooks as $webhook) {
                $result['relatedWebhooks'][] = [
                    'url' => $webhook->url,
                    'createdAt' => $webhook->created_at,
                    'statusCode' => $webhook->response_status_code,
                    'request' => $webhook->request,
                    'response' => $webhook->response,
                ];
            }
        }

        return $result;
    }

    private function getProcessingResponse(): array
    {
        $data = $this->getRate($this->resource->currency->code);
        $amountUsd = $this->inUsd($this->resource->transaction->amount, $data['rate']);

        $result = [
            'txId' => $this->resource->transaction->txId,
            'currency' => $this->resource->currency->code,
            'blockchain' => $this->resource->currency->blockchain,
            'contractAddress' => $this->resource->currency->contract_address,
            'amount' => $this->resource->transaction->amount,
            'amountUsd' => $amountUsd,
            'rate' => $data['rate'],
            'time' => $this->resource->transaction->time,
            'sender' => $this->resource->transaction->sender,
            'receiver' => $this->resource->transaction->receiver,
            'status' => TransactionStatus::Waiting->value,
        ];

        $invoices = [];
        if ($this->resource->probablyRelatedInvoices) {
            foreach ($this->resource->probablyRelatedInvoices as $invoice) {
                $store = $invoice->store;

                $invoices[] = [
                    'id' => $invoice->id,
                    'orderId' => $invoice->order_id,
                    'store' => [
                        'id' => $store->id ?? null,
                        'title' => $store->name ?? null,
                    ],
                    'createdAt' => $invoice->created_at,
                    'expiredAt' => $invoice->expired_at,
                    'amount' => $invoice->amount,
                    'status' => $invoice->status->value,
                ];
            }
        }
        if ($this->resource->payer) {
            $result['payer'] = [
                'id' => $this->resource?->payer->id,
                'storeId' => $this->resource?->payer->store_user_id,
                'store' => $this->resource?->payer->store->id,
                'storeName' => $this->resource?->payer->store->name,
            ];
        }

        $result['probablyRelatedInvoices'] = $invoices;

        return $result;
    }

    private function getRate(CurrencySymbol $currencyCode): ?array
    {
        $currencyService = app(CurrencyRateService::class);

        return $currencyService->getCurrencyRate(RateSource::Binance, CurrencySymbol::USDT, $currencyCode);
    }

    private function inUsd(string $amount, string $rate): string
    {
        $currencyConversion = app(CurrencyConversion::class);

        return $currencyConversion->convert($amount, $rate);
    }

}