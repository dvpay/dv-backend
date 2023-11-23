<?php

namespace App\Http\Controllers;

use App\Enums\Blockchain;
use App\Enums\RateSource;
use App\Exceptions\RateNotFoundException;
use App\Exceptions\ServiceUnavailableException;
use App\Http\Requests\Payer\PayerAddressRequest;
use App\Http\Requests\Payer\PayerStoreRequest;
use App\Http\Requests\Payer\PayerStoreWithApiKeyRequest;
use App\Http\Resources\Invoice\ListInvoicesCollection;
use App\Http\Resources\Payer\PayerAddressResource;
use App\Http\Resources\Payer\PayerCollection;
use App\Http\Resources\Payer\PayerExternalResource;
use App\Http\Resources\Payer\PayerResource;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\Payer;
use App\Models\Store;
use App\Repositories\StoreRepository;
use App\Services\Currency\CurrencyRateService;
use App\Services\Payer\PayerAddressService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class PayerController extends Controller
{
    public function __construct(
        private readonly StoreRepository     $storeRepository,
        private readonly PayerAddressService $payerAddressService,
        private readonly CurrencyRateService $currencyService,

    )
    {
    }

    public function index(Request $request, Authenticatable $user): PayerCollection
    {
        $payers = Payer::whereIn('store_id', $user->allStores()->pluck('id'))
            ->with(['store', 'payerAddresses'])
            ->paginate($request->input('perPage'));

        return PayerCollection::make($payers);
    }

    /**
     * @param PayerStoreRequest $request
     * @param Authenticatable $user
     * @return void
     * @throws AuthenticationException
     */
    public function store(PayerStoreRequest $request, Authenticatable $user): PayerResource
    {
        $store = $this->storeRepository->getStoreById($request->input('storeId'));

        if (!$store->status) {
            throw new ServiceUnavailableException(message: "Store inactive");
        }

        if (!$user->allStores()->contains('id', $store->id)) {
            throw new AuthenticationException(__("You don't have permission to this action!"));
        }

        $payer = Payer::firstOrCreate([
            'store_user_id' => $request->input('storeUserId'),
            'store_id'      => $store->id,
        ]);

        return PayerResource::make($payer);
    }

    /**
     * @param PayerStoreWithApiKeyRequest $request
     * @return PayerResource
     */
    #[OA\Post(
        path: '/payer/create',
        summary: 'Create Payer for static address',
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "storeUserId",
                            description: "unique user ID in your store",
                            type: "string")
                    ],
                    type: "object"
                )
            ]
        ),
        tags: ['payer'],
        parameters: [
            new OA\Parameter(name: 'X-Api-Key', in: 'header', required: true,
                schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: "Payer created", content: new OA\JsonContent(
                example: '{"result":{"id":"9ff39a38-71e1-4a27-83f6-65312691e28e","store_id":"9fe169fc-638c-4387-8477-8bf0e9268248","store_user_id":"1","payerUrl":"https:\/\/dv.net\/invoices\/payer\/9ff39a38-71e1-4a27-83f6-65312691e28e","store":{"id":"9fe169fc-638c-4387-8477-8bf0e9268248","name":"3321","status":1,"staticAddress":1,"storeCurrencyCode":"USD"},"address":[{"blockchain":"bitcoin","currency":"BTC.Bitcoin","address":"bc1qwzefc7fp8tdlnv0es3pk6snad22hhet56c300461","payer":{"id":"9ff39a38-71e1-4a27-83f6-65312691e28e","storeUserId":"1","payerUrl":"https:\/\/dv.net\/invoices\/payer\/9ff39a38-71e1-4a27-83f6-65312691e28e"}}]},"errors":[]}'
            )),
            new OA\Response(response: 422, description: "Invalid input data", content: new OA\JsonContent(
                example: '{"message":"The payer can only contain uppercase and lowercase letters, a number and a symbol \'-\'","errors":{"storeUserId":["The payer can only contain uppercase and lowercase letters, a number and a symbol \'-\'"]}}'
            )),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(
                example: '{"errors":["You don\'t have permission to this action!"],"result":[]}'
            )),
            new OA\Response(response: 503, description: "Store inactive", content: new OA\JsonContent(
                example: '{"errors":["Store inactive"],"result":[]}'
            )),
        ]
    )]
    public function createWithApikey(PayerStoreWithApiKeyRequest $request): PayerExternalResource
    {
        $store = $this->storeRepository->getStoreByApiKey($request->header('X-Api-Key'));

        if (!$store->status) {
            throw new ServiceUnavailableException(message: "Store inactive");
        }

        $payer = Payer::firstOrCreate([
            'store_user_id' => $request->input('storeUserId'),
            'store_id'      => $store->id,
        ]);

        return PayerExternalResource::make($payer);
    }

    /**
     * @param Payer $payer
     * @return PayerResource
     */
    public function show(Payer $payer): PayerResource
    {
        $payer->load(['store', 'payerAddresses']);
        $currencies = Currency::whereIn('blockchain', Blockchain::cases())
            ->where('has_balance', true)
            ->get();

        $rate = [];
        foreach ($currencies as $currency) {
            $rate[$currency->id] = $this->getCurrencyRate($payer->store, $currency);
        }

        return PayerResource::make($payer)->setRate($rate);
    }

    public function payerAddress(Payer $payer, PayerAddressRequest $request)
    {
        $store = $payer->store;

        if (!$store->status) {
            throw new ServiceUnavailableException(message: "Store inactive");
        }

        if (!$store->static_addresses) {
            throw ValidationException::withMessages([__('Static address generation is disabled in store settings')]);
        }

        $currency = Currency::where('id', $request->input('currency'))
            ->firstOrFail();
        $rate = $this->getCurrencyRate($store, $currency);
        $address = $this->payerAddressService->address($payer, $currency, $store);
        $address->rate = $rate;

        return PayerAddressResource::make($address);
    }

    public function invoices(Payer $payer, Authenticatable $user, Request $request)
    {
        if (!$user->allStores()->contains('id', $payer->store->id)) {
            throw new AuthenticationException(__("You don't have permission to this action!"));
        }

        $invoices = Invoice::where('payer_id', $payer->id)
            ->paginate($request->input('perPage'));

        return ListInvoicesCollection::make($invoices);
    }

    private function getCurrencyRate(Store $store, Currency $currency): ?string
    {
        $rateSource = RateSource::fromStore($store);

        $data = $this->currencyService->getCurrencyRate(
            $rateSource,
            $store->currency->code,
            $currency->code,
        );

        if (!$data) {
            throw new RateNotFoundException();
        }

        if ($currency->blockchain == Blockchain::Bitcoin) {
            $scale = bcmul($data['rate'], bcdiv($store->rate_scale, '100'));
            $data['rate'] = bcadd($data['rate'], -$scale);
        }

        return $data['rate'];
    }
}
