<?php

declare(strict_types=1);

namespace App\Services\Webhook;

use App\Dto\Models\WebhookDto;
use App\Dto\Webhook\SendWebhookDto;
use App\Dto\Webhook\TestWebhookDto;
use App\Enums\Blockchain;
use App\Enums\CurrencySymbol;
use App\Enums\HttpMethod;
use App\Enums\InvoiceStatus;
use App\Enums\WebhookType;
use App\Models\Store;
use App\Models\Webhook;
use DateTime;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class WebhookService
{
    public function __construct(private readonly WebhookSender $sender)
    {
    }

    public function create(WebhookDto $dto): Webhook
    {
        $webhook = Webhook::create($dto->toSnakeCase());

        return $webhook;
    }

    /**
     * @param Store $store
     * @return Collection Webhook model
     */
    public function list(Store $store): Collection
    {
        $webhooks = Webhook::where('store_id', $store->id)->get();

        return $webhooks;
    }

    public function update(WebhookDto $dto, Webhook $webhook): Webhook
    {
        $webhook->update($dto->toSnakeCase());

        return $webhook;
    }

    public function delete(Webhook $webhook): ?bool
    {
        return $webhook->delete();
    }

    public function test(Webhook $webhook, TestWebhookDto $dto): array
    {
        $webhookDto = new SendWebhookDto([
            'method' => HttpMethod::POST,
            'uri' => $webhook->url,
            'data' => $this->getMockWebhookData($dto),
            'secret' => $webhook->secret,
        ]);

        try {
            $response = $this->sender->send($webhookDto);

            $result = [
                'statusCode' => $response->getStatusCode(),
                'body' => $response->getBody()->getContents(),
            ];
        } catch (Throwable $e) {
            $result = [
                'statusCode' => $e->getCode(),
                'body' => $e->getMessage(),
            ];
        }

        return $result;
    }

    private function getMockWebhookData(TestWebhookDto $dto): array
    {
        $dt = new DateTime();

        switch ($dto->webhookType) {
            case WebhookType::InvoiceCreated:
                $status = InvoiceStatus::Waiting;
                $receiveAmount = 0;
                $paidAt = $dt->format('Y-m-d H:i:s');
                $transaction = [];
                break;

            case WebhookType::PaymentReceived:
                $status = InvoiceStatus::Paid;
                $receiveAmount = 100;
                $paidAt = $dt->format('Y-m-d H:i:s');
                $transaction = [
                    [
                        'txId' => 'a2b673e0011216f06052e96483cd39c0c47889b1097ec243c39456d1f1d68613',
                        'createdAt' => $dt->format('Y-m-d H:i:s'),
                        'currency' => CurrencySymbol::BTC,
                        'blockchain' => Blockchain::Bitcoin,
                        'amount' => 0.005,
                        'amountUsd' => 100,
                        'rate' => 20000,
                    ],
                ];
                break;

            case WebhookType::InvoiceExpired:
                $status = InvoiceStatus::Expired;
                $receiveAmount = 0;
                $paidAt = null;
                $transaction = [];
                break;
        }

        $data = [
            'orderId' => $dto->orderId,
            'status' => $status,
            'createdAt' => $dt->format('Y-m-d H:i:s'),
            'paidAt' => $paidAt,
            'expiredAt' => $dt->format('Y-m-d H:i:s'),
            'amount' => 100,
            'receivedAmount' => $receiveAmount,
            'transactions' => $transaction,
        ];

        return $data;
    }
}