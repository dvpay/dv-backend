<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\Models\WebhookDto;
use App\Dto\Webhook\TestWebhookDto;
use App\Enums\WebhookType;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\Webhook\CreateWebhookRequest;
use App\Http\Requests\Webhook\TestWebhookRequest;
use App\Http\Requests\Webhook\UpdateWebhookRequest;
use App\Http\Resources\DefaultResponseResource;
use App\Http\Resources\Webhook\WebhookCollection;
use App\Http\Resources\Webhook\WebhookResource;
use App\Jobs\WebhookJob;
use App\Models\Invoice;
use App\Models\Store;
use App\Models\Webhook;
use App\Services\Webhook\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends ApiController
{
    public function __construct(
        private readonly WebhookService $webhookService
    )
    {
    }

    public function create(CreateWebhookRequest $request, Store $store): JsonResponse
    {
        if ($request->user()->cannot('create', $store)) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        $input = $request->input();
        $input['storeId'] = $store->id;

        $dto = new WebhookDto($input);

        $webhook = $this->webhookService->create($dto);

        return (new WebhookResource($webhook))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function list(Request $request, Store $store): JsonResponse
    {
        if ($request->user()->cannot('view', $store)) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        $webhooks = $this->webhookService->list($store);

        return (new WebhookCollection($webhooks))
            ->response();
    }

    public function update(UpdateWebhookRequest $request, Store $store, Webhook $webhook): JsonResponse
    {
        if ($request->user()->cannot('update', [$webhook, $store])) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        $input = $request->input();
        $input['storeId'] = $store->id;

        $dto = new WebhookDto($input);

        $webhook = $this->webhookService->update($dto, $webhook);

        return (new WebhookResource($webhook))
            ->response();
    }

    public function delete(Request $request, Store $store, Webhook $webhook): JsonResponse
    {
        if ($request->user()->cannot('update', [$webhook, $store])) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        $this->webhookService->delete($webhook);

        return (new DefaultResponseResource([]))
            ->response()
            ->setStatusCode(Response::HTTP_NO_CONTENT);
    }

    public function test(TestWebhookRequest $request, Store $store, Webhook $webhook): JsonResponse
    {
        $input = $request->input();

        $dto = new TestWebhookDto([
            'webhookType' => WebhookType::tryFrom($input['eventType']),
            'orderId' => $input['orderId'],
        ]);

        $response = $this->webhookService->test($webhook, $dto);

        return (new DefaultResponseResource([
            'status' => $response['statusCode'],
            'message' => $response['body'],
        ]))->response();
    }
    public function sendWebhook(Invoice $invoice): DefaultResponseResource
    {
        Log::channel('supportLog')->info('Try send resend webhook for ' . $invoice->id);

        WebhookJob::dispatchSync($invoice, true);

        return (new DefaultResponseResource([]));
    }
}