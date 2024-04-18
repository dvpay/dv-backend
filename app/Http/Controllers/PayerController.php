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
use App\Http\Resources\Payer\PayerCollection;
use App\Http\Resources\Payer\PayerExternalResource;
use App\Http\Resources\Payer\PayerResource;
use App\Http\Resources\Payer\PayerTransactionsResource;
use App\Http\Resources\Payer\PayerUndisclosesAddressResource;
use App\Http\Resources\Payer\PayerUndisclosesResource;
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
        security: [["apiKeyAuth" => []]],
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
        tags: ['Payer'],
        responses: [
            new OA\Response(response: 200, description: "Payer created", content: new OA\JsonContent(
                example: '{"result":{"id":"9ff39a38-71e1-4a27-83f6-65312691e28e","store_id":"9fe169fc-638c-4387-8477-8bf0e9268248","store_user_id":"1","payerUrl":"https:\/\/dv.net\/invoices\/payer\/9ff39a38-71e1-4a27-83f6-65312691e28e","store":{"id":"9fe169fc-638c-4387-8477-8bf0e9268248","name":"3321","status":1,"staticAddress":1,"storeCurrencyCode":"USD"},"address":[{"blockchain":"bitcoin","currency":"BTC.Bitcoin","address":"bc1qwzefc7fp8tdlnv0es3pk6snad22hhet56c300461","payer":{"id":"9ff39a38-71e1-4a27-83f6-65312691e28e","payerUrl":"https:\/\/dv.net\/invoices\/payer\/9ff39a38-71e1-4a27-83f6-65312691e28e"}}]},"errors":[]}'
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
     * @return PayerUndisclosesResource
     */
    #[OA\Get(
        path: '/payer/{payer}',
        summary: 'Get payer info',
        tags: ['Payer'],
        parameters: [
            new OA\Parameter(name: 'payer', description: 'Your unique user ID', in: 'path', required: true,
                schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: "Get static address", content: new OA\JsonContent(
                example: '{"result":{"id":"3a9b350c-1ad9-4b8d-b5d0-a030f81cc745","store_id":"a09629db-fc33-4687-899e-0a709cb216da","store":{"id":"a09629db-fc33-4687-899e-0a709cb216da","name":"Karlee Spencer","status":1,"staticAddress":0,"storeCurrencyCode":"USD","returnUrl":null,"successUrl":null,"siteUrl":"http:\/\/abshire.com\/"},"address":[{"blockchain":"bitcoin","currency":"BTC.Bitcoin","address":"a8089cf05a4ebfd3beec75e4277766c7","rate":null,"payer":{"id":"3a9b350c-1ad9-4b8d-b5d0-a030f81cc745","store":"a09629db-fc33-4687-899e-0a709cb216da","storeName":"Karlee Spencer","payerUrl":"\/payer\/3a9b350c-1ad9-4b8d-b5d0-a030f81cc745"},"transactions":[],"unconfirmedTransactions":[{"id":2,"user_id":7,"store_id":"6b62fa8f-187a-4e26-a751-6bb08f84e5ae","invoice_id":"64d49520-5abd-4f54-8a77-77f6c1bac38e","from_address":"357983dbc25f58954c74833504ac9f3e","to_address":"a8089cf05a4ebfd3beec75e4277766c7","tx_id":"74f5e33ada1a9cd7c1491bd104f3feaf33025fc1","currency_id":"BTC.Bitcoin","created_at":"2024-02-06T09:50:34.000000Z","updated_at":"2024-02-06T09:50:34.000000Z"}]},{"blockchain":"tron","currency":"USDT.Tron","address":"53d7b611f0a023b0b9a54e6c15df77d6","rate":null,"payer":{"id":"3a9b350c-1ad9-4b8d-b5d0-a030f81cc745","storeUserId":"3","store":"a09629db-fc33-4687-899e-0a709cb216da","storeName":"Karlee Spencer","payerUrl":"\/payer\/3a9b350c-1ad9-4b8d-b5d0-a030f81cc745"},"transactions":[],"unconfirmedTransactions":[{"id":1,"user_id":4,"store_id":"7f54d804-7fcb-44ed-99ba-40e9b21fa839","invoice_id":"f6fbc528-0d91-4fba-b5ba-b56da085ab99","from_address":"f3f9d3d4f0a5fc353471dd35d2a8912f","to_address":"53d7b611f0a023b0b9a54e6c15df77d6","tx_id":"6883e5ba3b388bc257e4816929e6a473f2861ae4","currency_id":"USDT.Tron","created_at":"2024-02-06T09:50:34.000000Z","updated_at":"2024-02-06T09:50:34.000000Z"}]}],"currency":["BTC.Bitcoin","USDT.Tron"],"rate":{"BTC.Bitcoin":"0.99000000","USDT.Tron":"1"},"payerUrl":"\/payer\/3a9b350c-1ad9-4b8d-b5d0-a030f81cc745"},"errors":[]}',
            )),
        ],

    )]
    public function show(Payer $payer): PayerUndisclosesResource
    {
        $payer->load(['store', 'payerAddresses']);
        $currencies = Currency::whereIn('blockchain', Blockchain::cases())
            ->where('has_balance', true)
            ->get();

        $rate = [];
        foreach ($currencies as $currency) {
            $rate[$currency->id] = $this->getCurrencyRate($payer->store, $currency);
        }

        return PayerUndisclosesResource::make($payer)->setRate($rate);
    }

    public function payerAddress(Payer $payer, PayerAddressRequest $request)
    {
        $store = $payer->store;

        $currency = Currency::where('id', $request->input('currency'))
            ->firstOrFail();
        $rate = $this->getCurrencyRate($store, $currency);
        $address = $this->payerAddressService->address($payer, $currency, $store);
        $address->rate = $rate;

        return PayerUndisclosesAddressResource::make($address);
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

            $scale = -$scale;

            if (preg_match('/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/', $scale)) {
                // Convert $amount from exponential notation to a decimal string
                $scale = sprintf("%.10f", $scale);
            }

            $data['rate'] = bcadd($data['rate'],  $scale);
        }

        return $data['rate'];
    }
}
