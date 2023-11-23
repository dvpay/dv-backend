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
use App\Models\HotWallet;
use App\Services\HotWallet\HotWalletServiceInterface;
use Illuminate\Contracts\Auth\Authenticatable;

class HotWalletController extends Controller
{
    public function __construct(
        private readonly HotWalletServiceInterface $hotWalletService,
    )
    {
    }

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
}
