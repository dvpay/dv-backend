<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Dto\CreateInvoiceDto;
use App\Dto\GetListInvoicesDto;
use App\Dto\InvoiceAddressesListDto;
use App\Dto\InvoiceListByAddressDto;
use App\Exceptions\ServiceUnavailableException;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\Invoice\GetListInvoicesRequest;
use App\Http\Requests\Invoice\InvoiceAddressesListRequest;
use App\Http\Requests\Invoice\InvoiceCreateWithApiKeyRequest;
use App\Http\Requests\Invoice\InvoiceCreateWithAuthKeyRequest;
use App\Http\Requests\Invoice\SaveEmailRequest;
use App\Http\Resources\DefaultResponseResource;
use App\Http\Resources\Invoice\CreateInvoiceResource;
use App\Http\Resources\Invoice\DetailInvoiceResource;
use App\Http\Resources\Invoice\GetInvoiceResource;
use App\Http\Resources\Invoice\ListInvoiceAddressesCollection;
use App\Http\Resources\Invoice\ListInvoicesByAddressCollection;
use App\Http\Resources\Invoice\ListInvoicesCollection;
use App\Models\Currency;
use App\Models\HotWallet;
use App\Models\Invoice;
use App\Models\Store;
use App\Repositories\CurrencyRepository;
use App\Repositories\StoreRepository;
use App\Services\Currency\CurrencyConversion;
use App\Services\Invoice\InvoiceAddressCreator;
use App\Services\Invoice\InvoiceAddressService;
use App\Services\Invoice\InvoiceCreator;
use App\Services\Invoice\InvoiceService;
use Exception;
use Illuminate\Cache\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Throwable;

/**
 * Class InvoiceController
 */
class InvoiceController extends ApiController
{
    /**
     * @param InvoiceService $invoiceService
     * @param InvoiceAddressService $invoiceAddressService
     * @param InvoiceCreator $invoiceCreator
     * @param InvoiceAddressCreator $invoiceAddressCreator
     * @param StoreRepository $storeRepository
     * @param CurrencyConversion $currencyConversion
     * @param CurrencyRepository $currencyRepository
     * @param Repository $cache
     * @param string $url
     * @param array $disabledBlockchains
     */
    public function __construct(
        private readonly InvoiceService           $invoiceService,
        private readonly InvoiceAddressService    $invoiceAddressService,
        private readonly InvoiceCreator           $invoiceCreator,
        private readonly InvoiceAddressCreator    $invoiceAddressCreator,
        private readonly StoreRepository          $storeRepository,
        private readonly CurrencyConversion       $currencyConversion,
        private readonly CurrencyRepository       $currencyRepository,
        private readonly Repository               $cache,
        private readonly string                   $url,
        private readonly array                    $disabledBlockchains
    )
    {
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    #[OA\Post(
        path: '/invoices',
        summary: 'Create invoice',
        security: [["apiKeyAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "orderId", description: "Shop order id",
                            type: "string"),
                        new OA\Property(property: "amount", description: "Amount order",
                            type: "number"),
                        new OA\Property(property: "description", description: "description order",
                            type: "string"),
                        new OA\Property(property: "currency", description: "Order currency (fiat)",
                            type: "string"),
                        new OA\Property(property: "returnUrl",
                            description: "URI for return to shop", type: "string"),
                        new OA\Property(property: "successUrl",
                            description: "URI for success response", type: "string"),
                        new OA\Property(property: "paymentMethod", description: "Payment method",
                            type: "string"),
                    ],
                    type: "object",
                )
            ]

        ),
        tags: ['Invoice'],
        responses: [
            new OA\Response(response: 200, description: "Invoice created"),
            new OA\Response(response: 400, description: "Invalid input data"),
            new OA\Response(response: 401, description: "Unauthorized"),
            new OA\Response(response: 503, description: "Store inactive"),
        ]
    )]
    public function createWithApiKey(InvoiceCreateWithApiKeyRequest $request): JsonResponse
    {
        $store = $this->storeRepository->getStoreByApiKey($request->header('X-Api-Key'));

        if (!$store->status) {
            throw new ServiceUnavailableException(message: "Store inactive");
        }

        $input = $this->getInputForCreate($request, $store);
        $dto = new CreateInvoiceDto($input);

        $invoice = $this->invoiceCreator->store($dto, $store);

        return (new CreateInvoiceResource(
            $invoice,
            $this->url
        ))->response();
    }

    #[OA\Get(
        path: "/invoices/{invoiceId}",
        summary: "Get invoice info",
        tags: ['Invoice'],
        responses: [
            new OA\Response(response: 200, description: "Get invoice info"),
            new OA\Response(response: 400, description: "Invalid input data"),
            new OA\Response(response: 401, description: "Unauthorized"),
        ],

    )]
    public function detail(Invoice $invoice, Request $request): JsonResponse
    {
        $this->invoiceAddressCreator->updateRateInvoiceAddress($invoice);

        if (empty($invoice->user_agent)) {
            $this->invoiceService->writeUserAgent($invoice, $request);
        }

        return (new GetInvoiceResource(
            $invoice,
            $this->currencyConversion,
            $this->cache,
            $this->disabledBlockchains
        ))->response();
    }

    // todo add validate store permission user
    public function list(GetListInvoicesRequest $request): ListInvoicesCollection
    {
        $input = $request->input();

        $dto = new GetListInvoicesDto([
            'query'         => $input['query'] ?? null,
            'page'          => $input['page'] ?? 1,
            'perPage'       => $input['perPage'] ?? 10,
            'sortField'     => $input['sortField'] ?? 'created_at',
            'sortDirection' => $input['sortDirection'] ?? 'asc',
            'user'          => $request->user(),
            'stores'        => $input['stores'] ?? null,
        ]);

        $invoices = $this->invoiceService->invoiceList($dto);

        return new ListInvoicesCollection($invoices);
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function createWithAuthKey(InvoiceCreateWithAuthKeyRequest $request): JsonResponse
    {
        $store = $this->storeRepository->getStoreById($request->input('storeId'));

        if (!$store->status) {
            throw new ServiceUnavailableException(message: "Store inactive");
        }
        $input = $this->getInputForCreate($request, $store);

        $dto = new CreateInvoiceDto($input);

        $invoice = $this->invoiceCreator->store($dto, $store);

        return (new DefaultResponseResource([
            'id' => $invoice->id,
        ]))->response();
    }

    public function detailWithAuthKey(Request $request, Invoice $invoice): DetailInvoiceResource
    {
        $store = $invoice->store;
        if ($request->user()->cannot('view', [$invoice, $store])) {
            throw new UnauthorizedException(__("You don't have permission to this action!"));
        }

        return new DetailInvoiceResource($invoice, $store, $this->currencyConversion, $this->disabledBlockchains);
    }

    /**
     * @param Request $request
     * @param Store $store
     * @return mixed
     */
    private function getInputForCreate(Request $request, Store $store)
    {
        $input = $request->input();

        if (isset($input['currency'])) {
            $currency = $this->currencyRepository->getFiatCurrencyByCode($input['currency']);
            $input['currencyId'] = $currency->id;
        } else {
            $input['currencyId'] = $store->currency->id;
        }

        $input['returnUrl'] = $input['returnUrl'] ?? $store->return_url;
        $input['successUrl'] = $input['successUrl'] ?? $store->success_url;

        return $input;
    }



    /**
     * @param InvoiceAddressesListRequest $request
     * @return ListInvoiceAddressesCollection
     */
    public function invoiceAddressList(InvoiceAddressesListRequest $request): ListInvoiceAddressesCollection
    {
        $input = $request->input();

        $dto = new InvoiceAddressesListDto([
            'query'         => $input['query'] ?? null,
            'page'          => $input['page'] ?? 1,
            'perPage'       => $input['perPage'] ?? 10,
            'sortField'     => $input['sortField'] ?? 'created_at',
            'sortDirection' => $input['sortDirection'] ?? 'asc',
            'user'          => $request->user(),
            'stores'        => $input['stores'] ?? null,
        ]);

        $addresses = $this->invoiceAddressService->invoiceAddressesList($dto);

        return new ListInvoiceAddressesCollection($addresses);
    }


    /**
     * @param Request $request
     * @return ListInvoicesByAddressCollection
     */
    public function invoiceAddressDetail(Request $request, Authenticatable $user)
    {
        $input = $request->input();
        //todo rewrite to binding and make policy
        HotWallet::where([
            ['user_id', $user->id],
            ['address', $input['address']]
        ])->firstOrFail();

        $dto = new InvoiceListByAddressDto([
            'address'       => $input['address'],
            'stores'        => $input['stores'] ?? null,
            'page'          => $input['page'] ?? 1,
            'perPage'       => $input['perPage'] ?? 30,
            'sortField'     => $input['sortField'] ?? 'created_at',
            'sortDirection' => $input['sortDirection'] ?? 'desc',
        ]);

        $result = $this->invoiceAddressService->getInvoicesByAddress($dto);

        return new ListInvoicesByAddressCollection($result);
    }

    /**
     * @param Request $request
     * @param Invoice $invoice
     * @return DefaultResponseResource
     * @throws Throwable
     */
    public function invoiceConfirm(Request $request, Invoice $invoice)
    {
        $this->invoiceService->confirm($invoice);

        return new DefaultResponseResource([]);
    }

    /**
     * @throws Throwable
     */
    public function saveEmail(SaveEmailRequest $request, Invoice $invoice)
    {
        $email = $request->input('email') ?? null;
        $lang = app()->getLocale();

        $this->invoiceService->saveEmail($invoice, $email, $lang);

        return new DefaultResponseResource([]);
    }
}
