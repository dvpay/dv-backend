<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Exceptions\UnauthorizedException;
use App\Http\Requests\ApiKey\UpdateApiKeyRequest;
use App\Http\Resources\ApiKey\ApiKeyCollection;
use App\Http\Resources\ApiKey\ApiKeyResource;
use App\Http\Resources\DefaultResponseResource;
use App\Models\Store;
use App\Models\StoreApiKey;
use App\Services\ApiKey\ApiKeyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ApiKeyController
 */
class ApiKeyController extends ApiController
{
    /**
     * @param ApiKeyService $apiKeyService
     */
    public function __construct(
        private readonly ApiKeyService $apiKeyService
    )
    {
    }

    /**
     * @param Request $request
     * @param Store $store
     * @return JsonResponse
     */
    public function create(Request $request, Store $store): JsonResponse
    {
        if ($request->user()->cannot('create', $store)) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        $apiKey = $this->apiKeyService->create($store);

        return (new ApiKeyResource($apiKey))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @param Store $store
     * @return JsonResponse
     */
    public function list(Request $request, Store $store): JsonResponse
    {
        if ($request->user()->cannot('view', $store)) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        $apiKeys = $this->apiKeyService->list($store);

        return (new ApiKeyCollection($apiKeys))
            ->response();
    }

    /**
     * @param UpdateApiKeyRequest $request
     * @param Store $store
     * @param StoreApiKey $apiKey
     * @return JsonResponse
     */
    public function update(UpdateApiKeyRequest $request, Store $store, StoreApiKey $apiKey): JsonResponse
    {
        if ($request->user()->cannot('update', [$apiKey, $store])) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        $webhooks = $this->apiKeyService->updateEnabled($apiKey, $request->input('enabled'));

        return (new ApiKeyResource($webhooks))
            ->response();
    }

    /**
     * @param Request $request
     * @param Store $store
     * @param StoreApiKey $apiKey
     * @return JsonResponse
     */
    public function delete(Request $request, Store $store, StoreApiKey $apiKey): JsonResponse
    {
        if ($request->user()->cannot('update', [$apiKey, $store])) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        $this->apiKeyService->delete($apiKey);

        return (new DefaultResponseResource([]))
            ->response()
            ->setStatusCode(Response::HTTP_NO_CONTENT);
    }
}