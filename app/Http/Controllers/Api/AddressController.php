<?php

namespace App\Http\Controllers\Api;

use App\Enums\Blockchain;
use App\Enums\RateSource;
use App\Exceptions\ApiException;
use App\Exceptions\RateNotFoundException;
use App\Exceptions\ServiceUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payer\PayerAddressShowRequest;
use App\Http\Requests\Payer\PayerStoreWithApiKeyRequest;
use App\Http\Resources\Payer\PayerAddressCollection;
use App\Http\Resources\Payer\PayerAddressResource;
use App\Models\Currency;
use App\Models\Payer;
use App\Models\Store;
use App\Repositories\StoreRepository;
use App\Services\Currency\CurrencyRateService;
use App\Services\Payer\PayerAddressService;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class AddressController extends Controller
{
    public function __construct(
        private readonly StoreRepository     $storeRepository,
        private readonly PayerAddressService $payerAddressService,
        private readonly CurrencyRateService $currencyService,
    )
    {
    }

    /**
     * @param PayerAddressShowRequest $request
     * @return PayerAddressResource
     */
    #[OA\Get(
        path: '/address/{payer}/{currency}',
        summary: 'Get static address for payer',
        security: [["apiKeyAuth" => []]],
        tags: ['Payer'],
        parameters: [
            new OA\Parameter(name: 'payer', description: 'Your unique user ID', in: 'path', required: true,
                schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'currency', description: 'Currency example BTC.Bitcoin, USDT.Tron', in: 'path', required: true,
                schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'email', description: 'Email for send address', in: 'query', required: false,
                schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: "Get static address", content: new OA\JsonContent(
                example: '{"result":{"blockchain":"bitcoin","currency":"BTC.Bitcoin","address":"bc1qwzefc7fp8tdlnv0es3pk6snad22hhet56c300461","payer":{"id":"9ff39a38-71e1-4a27-83f6-65312691e28e","storeUserId":"1"}},"errors":[]}',
            )),
            new OA\Response(response: 422, description: "Invalid route params", content: new OA\JsonContent(
                example: '{"message":"The payer can only contain uppercase and lowercase letters, a number and a symbol \'-\' (and 1 more error)","errors":{"payer":["The payer can only contain uppercase and lowercase letters, a number and a symbol \'-\'"],"currency":["Currency not found"]}}'
            )),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(
                example: '{"errors":["You don\'t have permission to this action!"],"result":[]}'
            )),
            new OA\Response(response: 403, description: "Static address generation is disabled in store settings", content: new OA\JsonContent(
                example: '{"errors":["Static address generation is disabled in store settings"],"result":[]}'
            )),
            new OA\Response(response: 503, description: "Store inactive", content: new OA\JsonContent(
                example: '{"errors":["Store inactive"],"result":[]}'
            )),
        ],

    )]
    public function getAddress(PayerAddressShowRequest $request)
    {
        $store = $this->storeRepository->getStoreByApiKey($request->header('X-Api-Key'));

        $currency = Currency::where('id', $request->input('currency'))
            ->firstOrFail();

        $payer = Payer::firstOrCreate([
            'store_user_id' => $request->input('payer'),
            'store_id'      => $store->id,
        ]);

        $address = $this->payerAddressService->address($payer, $currency, $store);

        $rate = $this->getCurrencyRate($store, $currency);
        $address->rate = $rate;

        if ($request->input('email')) {
            $this->payerAddressService->emailNotification($request->input('email'), [$address], $request->input('ip'));
        }

        return PayerAddressResource::make($address);
    }

    #[OA\Post(
        path: '/payer/addresses',
        summary: 'Get static address for payer in all currency',
        security: [["apiKeyAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "storeUserId",
                            description: "Your unique user ID in your store",
                            type: "string"),
                        new OA\Property(property: "email",
                            description: "Email for send address",
                            type: "string"),
                        new OA\Property(property: "ip",
                            description: "Ip user to display in email",
                            type: "string")
                    ],
                    type: "object"
                )
            ]
        ),
        tags: ['Payer'],
        responses: [
            new OA\Response(response: 200, description: "Payer created", content: new OA\JsonContent(
                example: '{"result": [{"blockchain": "bitcoin","currency": "BTC.Bitcoin","address": "bc1qwzefc7fp8tdlnv0es3pk6snad22hhet528d50e86","rate": "26568.16995000","payer": {"id": "de95717c-9814-4887-ba6b-94fc31eb6973","storeUserId": "1232143","payerUrl": "https://dv.net/invoices/payer/de95717c-9814-4887-ba6b-94fc31eb6973"}},{"blockchain": "tron","currency": "USDT.Tron","address": "TKn5GuNb62KgQh7SLXznUrP33Nae28d50e86","rate": "1","payer": {"id": "de95717c-9814-4887-ba6b-94fc31eb6973","storeUserId": "1232143","payerUrl": "https://dv.net/invoices/payer/de95717c-9814-4887-ba6b-94fc31eb6973"}}],"errors": []}'
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
    public function getAddresses(PayerStoreWithApiKeyRequest $request)
    {
        $store = $this->storeRepository->getStoreByApiKey($request->header('X-Api-Key'));

        if (!$store->status) {
            throw new ServiceUnavailableException(message: "Store inactive");
        }

        if (!$store->static_addresses) {
            throw new ApiException(__('Static address generation is disabled in store settings'), Response::HTTP_FORBIDDEN);
        }

        $payer = Payer::firstOrCreate([
            'store_user_id' => $request->input('storeUserId'),
            'store_id'      => $store->id,
        ]);
        $addresses = $this->payerAddressService->getAllStaticAddressesForUser($payer, $store);

        foreach ($addresses as $address) {
            $address->rate = $this->getCurrencyRate($store, $address->currency);
        }

        if ($request->input('email')) {
            $this->payerAddressService->emailNotification($request->input('email'), $addresses, $request->input('ip'));
        }

        return PayerAddressCollection::make($addresses);
    }

    // todo remove doublicate
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
