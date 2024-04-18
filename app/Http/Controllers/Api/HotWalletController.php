<?php

namespace App\Http\Controllers\Api;

use App\Dto\HotWallet\HotWalletsListDto;
use App\Enums\Blockchain;
use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\HotWallet\HotWalletsRequest;
use App\Http\Resources\DefaultResponseResource;
use App\Http\Resources\HotWallet\HotWalletCollection;
use App\Http\Resources\HotWallet\HotWalletDetailResource;
use App\Http\Resources\HotWallet\HotWalletSummaryCollection;
use App\Models\HotWallet;
use App\Services\HotWallet\HotWalletServiceInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use OpenApi\Attributes as OA;

class HotWalletController extends Controller
{
    public function __construct(
        private readonly HotWalletServiceInterface $hotWalletService,
    )
    {
    }

    #[OA\Get(
        path: "/hot-wallets",
        summary: "Get current user hot wallets list info",
        security: [["bearerAuth" => []]],
        tags: ['Hot Wallets'],
        parameters: [
            new OA\Parameter(name: 'page', description: 'Page number', in: 'query', required: false,
                schema: new OA\Schema(type: 'integer',example: 1)),
            new OA\Parameter(name: 'perPage', description: 'Items per page', in: 'query', required: false,
                schema: new OA\Schema(type: 'integer',example: 10)),
            new OA\Parameter(name: 'hideEmpty', description: 'Hides empty wallets', in: 'query', required: false,
                schema: new OA\Schema(type: 'integer',example: 1)),
            new OA\Parameter(name: 'sortDirection', description: 'Sort direction', in: 'query', required: false,
                schema: new OA\Schema(type: 'string',example: 'asc')),
            new OA\Parameter(name: 'filterField', description: 'Filter field name', in: 'query', required: false,
                schema: new OA\Schema(type: 'string',example: 'currency_id')),
            new OA\Parameter(name: 'filterValue', description: 'Filter value', in: 'query', required: false,
                schema: new OA\Schema(type: 'string',example: 'BTC.Bitcoin')),
        ],
        responses: [
            new OA\Response(response: 200, description: "Get current user detail info", content: new OA\JsonContent(
                example: '{"result":[{"id":54,"currency":"BTC.Bitcoin","currencyId":"BTC.Bitcoin","address":"7ee5e0169e55c2673a0ad36bf042c184","blockchain":"bitcoin","state":"busy","createdAt":"2024-01-31T06:46:43.000000Z","updatedTt":"2024-01-31T06:46:43.000000Z","balance":"530786556.28143000","balanceUsd":"3548555.86878740","explorerUrl":"https:\/\/www.blockchain.com\/explorer\/addresses\/btc\/7ee5e0169e55c2673a0ad36bf042c184"},{"id":86,"currency":"USDT.Tron","currencyId":"USDT.Tron","address":"ae7853ec2627d1e1fa12315e9ef21160","blockchain":"tron","state":"busy","createdAt":"2024-01-31T06:46:44.000000Z","updatedTt":"2024-01-31T06:46:44.000000Z","balance":"527531707.05976000","balanceUsd":"29.25225447","explorerUrl":"https:\/\/tronscan.org\/#\/address\/ae7853ec2627d1e1fa12315e9ef21160"}],"links":{"first":"http:\/\/api.merchant.local\/hot-wallets?page=1","last":"http:\/\/api.merchant.local\/hot-wallets?page=2","prev":null,"next":"http:\/\/api.merchant.local\/hot-wallets?page=2"},"meta":{"current_page":1,"from":1,"last_page":2,"links":[{"url":null,"label":"&laquo;Previous","active":false},{"url":"http:\/\/api.merchant.local\/hot-wallets?page=1","label":"1","active":true},{"url":"http:\/\/api.merchant.local\/hot-wallets?page=2","label":"2","active":false},{"url":"http:\/\/api.merchant.local\/hot-wallets?page=2","label":"Next&raquo;","active":false}],"path":"http:\/\/api.merchant.local\/hot-wallets","per_page":100,"to":100,"total":114},"errors":[]}',
            )),
            new OA\Response(response: 401, description: "Unauthorized",content: new OA\JsonContent(
                example: '{"errors":["Unauthenticated."],"result":[]}'
            )),
        ],

    )]
    public function index(Authenticatable $user, HotWalletsRequest $request)
    {
        $dto = new HotWalletsListDto([
            'page'          => $request->input('page') ?? 1,
            'perPage'       => $request->input('perPage') ?? 10,
            'user'          => $user,
            'hideEmpty'     => $request->input('hideEmpty') ?? false,
            'sortDirection' => $request->input('sortDirection') ?? 'desc',
            'filterField'   => $request->input('filterField') ?? null,
            'filterValue'   => $request->input('filterValue') ?? null,
        ]);
        $hotWallets = $this->hotWalletService->userHotWallets($dto);

        return HotWalletCollection::make($hotWallets);
    }

    public function show(HotWallet $hotWallet)
    {
        $result = $hotWallet->loadCount([
            'invoices',
            'invoices as paid_invoices_count' => fn($query) => $query->whereIn('status', [InvoiceStatus::Paid->value, InvoiceStatus::PartiallyPaid->value]),
            'transactionsIncoming',
            'transactionsOutgoing'
        ])
            ->loadSum('transactionsIncoming', 'amount_usd')
            ->loadSum('transactionsOutgoing', 'amount_usd');

        return HotWalletDetailResource::make($result);
    }

    public function stats(Blockchain $blockchain, Authenticatable $user)
    {
        $result = HotWallet::selectRaw('count(*) as addressCount, SUM(amount_usd) as amountUsd')
            ->where('user_id', $user->id)
            ->where('blockchain', $blockchain)
            ->where('amount_usd', '>', 0)
            ->first();

        return DefaultResponseResource::make($result);
    }
    #[OA\Get(
        path: "/hot-wallets/summary",
        summary: "Get wallets summary info",
        security: [["bearerAuth" => []]],
        tags: ['Hot Wallets'],
        responses: [
            new OA\Response(response: 200, description: "Get current user detail info", content: new OA\JsonContent(
                example: '{"result":[{"currencyId":"BTC.Bitcoin","sumAmount":0.02125022,"sumAmountUsd":915.96,"countWithBalance":21,"createdCount":4017},{"currencyId":"USDT.Tron","sumAmount":3536.000319,"sumAmountUsd":3536,"countWithBalance":248,"createdCount":26889}],"errors":[]}',
            )),
            new OA\Response(response: 401, description: "Unauthorized",content: new OA\JsonContent(
                example: '{"errors":["Unauthenticated."],"result":[]}'
            )),
        ],
    )]
    public function summary(Authenticatable $user): HotWalletSummaryCollection
    {

        $result = $this->hotWalletService->getSummary($user);

        return HotWalletSummaryCollection::make($result);
    }
}
