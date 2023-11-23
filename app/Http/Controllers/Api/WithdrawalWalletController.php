<?php

namespace App\Http\Controllers\Api;

use App\Enums\ExchangeService;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\WithdrawalRequest;
use App\Http\Requests\Withdrawal\WithdrawalWalletUpdateRequest;
use App\Http\Resources\DefaultResponseResource;
use App\Http\Resources\Withdrawal\WithdrawalWalletCollection;
use App\Http\Resources\Withdrawal\WithdrawalWalletResource;
use App\Models\WithdrawalWallet;
use App\Services\Processing\Contracts\OwnerContract;
use App\Services\WithdrawalWallet\WithdrawalWalletService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class WithdrawalWalletController extends Controller
{
    public function __construct(
        public readonly WithdrawalWalletService $withdrawalWalletService,
        public readonly OwnerContract           $ownerContract,
    )
    {
    }

    public function index(Authenticatable $user)
    {
        if ($user->withdrawalWallets->isEmpty()) {
            $this->withdrawalWalletService->createWallets($user);
        }

        $wallets = WithdrawalWallet::where('user_id', $user->id)
            ->with('address')
            ->get();

        return WithdrawalWalletCollection::make($wallets);
    }

    public function show(WithdrawalWallet $withdrawalWallet)
    {
        $wallet = $withdrawalWallet->load(['address', 'exchange']);

        return WithdrawalWalletResource::make($wallet);
    }

    /**
     * @throws \Throwable
     */
    public function update(WithdrawalWalletUpdateRequest $request, WithdrawalWallet $withdrawalWallet)
    {
        DB::transaction(function () use ($withdrawalWallet, $request) {
            $withdrawalWallet->updateOrFail([
                'exchange_id'            => $request->input('exchangeSlug') ? ExchangeService::tryFrom($request->input('exchangeSlug'))->getId() : null,
                'type'                   => $request->input('addressType'),
                'withdrawal_enabled'     => $request->input('withdrawalEnabled'),
                'withdrawal_min_balance' => $request->input('withdrawalMinBalance'),
                'withdrawal_interval'    => $request->input('withdrawalInterval'),
            ]);
            $withdrawalWallet->address()->delete();
            foreach ($request->input('address') as $address) {
                $withdrawalWallet->address()->restoreOrCreate(
                    ['address' => $address],
                    ['withdrawal_wallet_id' => $withdrawalWallet->id]
                );
            }
            $this->ownerContract->attachColdWalletWithAddress(
                $withdrawalWallet->blockchain,
                $withdrawalWallet->user->processing_owner_id,
                $withdrawalWallet->address->pluck('address')->toArray()
            );
        });

        return DefaultResponseResource::make([]);
    }

    public function withdrawal(WithdrawalRequest $request, Authenticatable $user)
    {
        if ($request->user()->hasPermissionTo('transfer funds')) {
            throw new ApiException(__('Transfer disabled '), Response::HTTP_BAD_REQUEST);
        }

        $this->withdrawalWalletService->withdrawal($user, $request->input('chain'));

        return DefaultResponseResource::make([]);
    }

    public function withdrawalFromAddress(Request $request, Authenticatable $user)
    {
        if ($request->user()->hasPermissionTo('transfer funds')) {
            throw new ApiException(__('Transfer disabled '), Response::HTTP_BAD_REQUEST);
        }
        $this->withdrawalWalletService->withdrawalFromAddress($user, $request->input('currencyId'), $request->input('addressFrom'));

        return DefaultResponseResource::make([]);
    }

}
