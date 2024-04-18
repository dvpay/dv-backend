<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\Models\StoreDto;
use App\Enums\CurrencyId;
use App\Enums\CurrencySymbol;
use App\Enums\PermissionsEnum;
use App\Enums\RateSource;
use App\Exceptions\ApiException;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\Store\ChangeStatusStoreRequest;
use App\Http\Requests\Store\CreateStoreRequest;
use App\Http\Requests\Store\GetCurrencyRateRequest;
use App\Http\Requests\Store\UpdateRateSourceRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Http\Requests\Store\UpdateUrlsRequest;
use App\Http\Requests\Withdrawal\WithdrawalWalletWithdrawFromProcessingRequest;
use App\Http\Resources\DefaultResponseResource;
use App\Http\Resources\Store\ListStoreCollection;
use App\Http\Resources\Store\StoreResource;
use App\Http\Resources\Withdrawal\UnconfirmedWithdrawalsResource;
use App\Models\Store;
use App\Repositories\CurrencyRepository;
use App\Repositories\StoreRepository;
use App\Services\Currency\CurrencyConversion;
use App\Services\Currency\CurrencyRateService;
use App\Services\Processing\BalanceGetter;
use App\Services\Store\StoreService;
use App\Services\Withdrawal\UnconfirmedWithdrawals;
use App\Services\WithdrawalWallet\WithdrawalWalletService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use OpenApi\Attributes as OA;

/**
 * StoreController
 */
class StoreController extends ApiController
{
    /**
     * @param StoreService $storeService
     * @param CurrencyRateService $currencyService
     * @param CurrencyConversion $currencyConversion
     * @param CurrencyRepository $currencyRepository
     * @param WithdrawalWalletService $withdrawalWalletService
     * @param string $invoiceLifetime
     */
    public function __construct(
        private readonly StoreService            $storeService,
        private readonly CurrencyRateService     $currencyService,
        private readonly CurrencyConversion      $currencyConversion,
        private readonly CurrencyRepository      $currencyRepository,
        private readonly WithdrawalWalletService $withdrawalWalletService,
        private readonly string                  $invoiceLifetime,
    )
    {
        $this->authorizeResource(Store::class, 'store');
    }

    #[Pure]
    protected function resourceAbilityMap()
    {
        return array_merge(parent::resourceAbilityMap(), [
            'changeStatus' => 'bulkUpdate',
        ]);
    }

    #[Pure]
    protected function resourceMethodsWithoutModels()
    {
        return array_merge(parent::resourceMethodsWithoutModels(), ['changeStatus']);
    }

    /**
     * @throws Throwable
     */
    public function create(CreateStoreRequest $request): StoreResource
    {
        $currencyCode = $request->input('currency') ?? CurrencySymbol::USD->value;
        $currency = $this->currencyRepository->getFiatCurrencyByCode($currencyCode);

        $dto = new StoreDto([
            'name'                  => $request->input('name'),
            'currencyId'            => $currency->id,
            'rateSource'            => $request->input('rateSource') ?? RateSource::Binance->value,
            'rateScale'             => $request->input('rateScale') ?? config('setting.rate_scale'),
            'userId'                => $request->user()->id,
            'invoiceExpirationTime' => $this->invoiceLifetime,
            'addressHoldTime'       => config('setting.store_address_hold_time'),
            'status'                => true,
            'staticAddresses'       => true,
        ]);

        $store = $this->storeService->create($dto);

        return new StoreResource($store);
    }

    /**
     * @param Request $request
     *
     * @return ListStoreCollection
     */
    #[OA\Get(
        path: '/stores',
        summary: 'Get user stores list',
        security: [["bearerAuth" => []]],
        tags: ['Store'],
        responses: [
            new OA\Response(response: 200, description: "Get user stores list", content: new OA\JsonContent(
                example: '{"result":[{"id":"040e9dcf-40e5-4408-a395-7de1661a60d3","name":"MissKaelaBaumbach","createdAt":"2024-01-31T11:08:48+00:00","invoicesCount":0},{"id":"06053f59-562a-416c-9db4-902ec26ba228","name":"ConnerNolan","createdAt":"2024-01-31T13:20:28+00:00","invoicesCount":0}],"errors":[]}',
            )),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(
                example: '{"errors":["You don\'t have permission to this action!"],"result":[]}'
            )),
        ],

    )]
    public function list(Request $request): ListStoreCollection
    {
        $user = $request->user();

        $list = $this->storeService->list($user);

        return new ListStoreCollection($list);
    }

    /**
     * @param UpdateStoreRequest $request
     * @param Store $store
     *
     * @return JsonResponse
     */
    public function update(UpdateStoreRequest $request, Store $store): JsonResponse
    {
        if ($request->user()->cannot('update', $store)) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        $currency = $this->currencyRepository->getFiatCurrencyByCode($request->input('currency'));

        $dto = new StoreDto([
            'name'                  => $request->input('name'),
            'site'                  => $request->input('site'),
            'currencyId'            => $currency->id,
            'invoiceExpirationTime' => $request->input('invoiceExpirationTime'),
            'addressHoldTime'       => $request->input('addressHoldTime'),
            'status'                => $request->input('status'),
            'staticAddresses'       => $request->input('staticAddresses'),
            'minimalPayment'        => $request->input('minimalPayment'),
        ]);

        $store = $this->storeService->update($dto, $store);

        return (new StoreResource($store))
            ->response();
    }

    /**
     * @param UpdateRateSourceRequest $request
     * @param Store $store
     *
     * @return JsonResponse
     */
    public function rateSource(UpdateRateSourceRequest $request, Store $store): JsonResponse
    {
        if ($request->user()->cannot('update', $store)) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        $dto = new StoreDto([
            'rateSource' => $request->input('rateSource'),
        ]);

        $store = $this->storeService->update($dto, $store);

        return (new StoreResource($store))
            ->response();
    }

    /**
     * @param Request $request
     * @param Store $store
     *
     * @return JsonResponse
     */
    public function getStore(Request $request, Store $store): JsonResponse
    {
        if ($request->user()->cannot('view', $store)) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        return (new StoreResource($store))
            ->response();
    }

    /**
     * @param GetCurrencyRateRequest $request
     * @param Store $store
     *
     * @return JsonResponse
     */
    public function rate(GetCurrencyRateRequest $request, Store $store): JsonResponse
    {
        $input = $request->input();
        $from = CurrencySymbol::tryFrom($input['from']);
        $to = CurrencySymbol::tryFrom($input['to']);
        $rateSource = RateSource::fromStore($store);
        $data = $this->currencyService->getCurrencyRate($rateSource, $from, $to);

        $result = $this->currencyConversion->convert(
            amount: $input['amount'],
            rate: $data['rate']
        );

        return (new DefaultResponseResource([$result]))
            ->response();
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function balancesProcessingWallets(Request $request): JsonResponse
    {
        $store = app(StoreRepository::class)->getStoreByApiKey($request->header('X-Api-Key'));
        if ($request->user()->cannot('view', $store)) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        if (!$request->user()->processing_owner_id) {
            throw new ApiException(__("Bad request"), Response::HTTP_BAD_REQUEST);
        }

        return response()->json(app(BalanceGetter::class)->get($request->user()));
    }

    /**
     * @param Request $request
     *
     * @return UnconfirmedWithdrawalsResource
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function unconfirmedWithdrawals(Request $request): UnconfirmedWithdrawalsResource
    {
        $store = app(StoreRepository::class)->getStoreByApiKey($request->header('X-Api-Key'));
        if ($request->user()->cannot('view', $store)) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        return new UnconfirmedWithdrawalsResource(app(UnconfirmedWithdrawals::class)->get($store->id));
    }


    #[OA\Post(
        path: '/withdrawals/withdrawal-from-processing-wallet',
        summary: 'Withdraw From Processing Wallet',
        security: [["apiKeyAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "currencyId",
                            description: "Currency Id",
                            type: "string",
                            example: "USDT.Tron"
                        ),
                        new OA\Property(property: "addressTo",
                            description: "Address To Withdrawal",
                            type: "string",
                            example: "TBQhpoxrutttqgnrgDSvmcsGBb4Ac1oJDc"
                        ),
                        new OA\Property(property: "amount",
                            description: "Amount To Withdrawal",
                            type: "string",
                            example: "1"
                        ),
                    ],
                    type: "object"
                )
            ]
        ),
        tags: ['Withdrawal Wallet'],
        responses: [
            new OA\Response(response: 200, description: "Withdrawal Has Been Sent", content: new OA\JsonContent(
                example: '{"result":[],"errors":[]}'
            )),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(
                example: '{"errors":["You don\'t have permission to this action!"],"result":[]}'
            )),
        ]
    )]
    public function withdrawalFromProcessingWallet(WithdrawalWalletWithdrawFromProcessingRequest $request)
    {
        $store = app(StoreRepository::class)->getStoreByApiKey($request->header('X-Api-Key'));

        #TODO: Permissions seems to be useless
        if ($store->user->hasPermissionTo(PermissionsEnum::TransfersFunds->value)) {
            throw new ApiException(__('Transfer disabled '), Response::HTTP_BAD_REQUEST);
        }

        $this->withdrawalWalletService->withdrawalFromProcessingWallet(
            user: $store->user,
            currencyId: CurrencyId::from($request->input('currencyId')),
            addressTo: $request->input('addressTo'),
            amount: $request->input('amount'),
        );

        return DefaultResponseResource::make([]);
    }

    /**
     * @param Request $request
     * @param Store $store
     * @return JsonResponse
     */
    public function getUrls(Request $request, Store $store): JsonResponse
    {
        if ($request->user()->cannot('view', $store)) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        return (new DefaultResponseResource([
            "return_url"  => $store->return_url,
            "success_url" => $store->success_url,
        ]))->response();
    }

    /**
     * @param Request $request
     * @param Store $store
     * @return JsonResponse
     */
    public function setUrls(UpdateUrlsRequest $request, Store $store): JsonResponse
    {
        if ($request->user()->cannot('update', $store)) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        $dto = new StoreDto([
            'returnUrl'  => $request->input('returnUrl'),
            'successUrl' => $request->input('successUrl'),
        ]);

        $store = $this->storeService->update($dto, $store);

        return (new StoreResource($store))
            ->response();
    }

    public function changeStatus(ChangeStatusStoreRequest $request, Authenticatable $user)
    {
        $storeDto = new StoreDto([
            'status' => $request->input('status'),
        ]);

        $this->storeService->batchUpdateStore($storeDto, $user);

        $permission = 'stop pay';

        if ($request->input('status')) {
            $user->revokePermissionTo($permission);
        } else {
            $user->givePermissionTo($permission);
        }

        return (new DefaultResponseResource([]))
            ->response();
    }
}
