<?php

namespace App\Http\Controllers\Api\External;

use App\Enums\Blockchain;
use App\Enums\RateSource;
use App\Exceptions\RateNotFoundException;
use App\Exceptions\ServiceUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\RateStoreRequest;
use App\Http\Resources\DefaultResponseResource;
use App\Models\Currency;
use App\Repositories\StoreRepository;
use App\Services\Currency\CurrencyRateService;
use OpenApi\Attributes as OA;

class StoreController extends Controller
{
    #[OA\Get(
        path: '/v2/stores/currencies/rate',
        summary: 'Get currency rate',
        tags: ['store'],
        parameters: [
            new OA\Parameter(name: 'X-Api-Key', in: 'header', required: true,
                schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'currency', description: 'Currency example BTC.Bitcoin, ETH.Ethereum', in: 'query', required: true,
                schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: "Get store rate", content: new OA\JsonContent(
                example: '{"result":{"rateSource":"Binance","rate":"1714.99000000","lastUpdate":{"date":"2023-08-31 11:55:57.062434","timezone_type":3,"timezone":"UTC"}},"errors":[]}',
            )),
            new OA\Response(response: 401, description: "Unauthorized", content: new OA\JsonContent(
                example: '{"errors":["You don\'t have permission to this action!"],"result":[]}'
            )),
            new OA\Response(response: 404, description: "Static address generation is disabled in store settings", content: new OA\JsonContent(
                example: '{"errors":["Static address generation is disabled in store settings"],"result":[]}'
            )),
            new OA\Response(response: 503, description: "Store inactive", content: new OA\JsonContent(
                example: '{"errors":["Store inactive"],"result":[]}'
            )),
        ],

    )]
    public function rateStore(
        RateStoreRequest    $request,
        StoreRepository     $storeRepository,
        CurrencyRateService $currencyRateService,
    )
    {
        $store = $storeRepository->getStoreByApiKey($request->header('X-Api-Key'));

        if (!$store->status) {
            throw new ServiceUnavailableException(message: "Store inactive");
        }
        $currency = Currency::where('id', $request->input('currency'))
            ->firstOrFail();

        $rateSource = RateSource::fromStore($store);
        $data = $currencyRateService->getCurrencyRate(
            $rateSource,
            $store->currency->code,
            $currency->code,
        );

        if ($currency->blockchain == Blockchain::Bitcoin) {
            $scale = bcmul($data['rate'], bcdiv($store->rate_scale, '100'));
            $data['rate'] = bcadd($data['rate'], -$scale);
        }

        if (!$data) {
            throw new RateNotFoundException();
        }

        return new DefaultResponseResource([
            'rateSource' => $rateSource,
            'rate'       => $data['rate'],
            'lastUpdate' => $data['lastUpdate'],
        ]);
    }
}
