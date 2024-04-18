<?php

namespace App\Services\HotWallet;

use App\Dto\HotWallet\HotWalletDto;
use App\Dto\HotWallet\HotWalletsListDto;
use App\Models\HotWallet;
use App\Models\PayerAddress;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

class HotWalletService implements HotWalletServiceInterface
{

    public function userHotWallets(HotWalletsListDto $dto)
    {
        $query = HotWallet::where('user_id', $dto->user->id)
            ->with('currency');

        if ($dto->hideEmpty) {
            $query = $query->where('amount', '>', 0);
        }

        if (isset($dto->filterField)) {
            $query = $query->where($dto->filterField, '=', $dto->filterValue);
        }

        if ($dto->sortDirection) {
            $query = $query->orderBy('amount', $dto->sortDirection);
        }

        return $query->paginate($dto->perPage);
    }

    /**
     * @param HotWalletDto $dto
     * @return HotWallet
     */
    public function storeHotWallet(HotWalletDto $dto): HotWallet
    {
        return HotWallet::firstOrCreate([
            'address'     => $dto->address,
            'currency_id' => $dto->currencyId->value
        ], [
            'user_id'    => $dto->user->id,
            'blockchain' => $dto->blockchain->value,
            'amount'     => $dto->amount,
            'amount_usd' => $dto->amountUsd,
        ]);
    }

    public function updateOrCreate(HotWalletDto $dto): HotWallet
    {
        return HotWallet::updateOrCreate([
            'address' => $dto->address,
            'currency_id' => $dto->currencyId->value,
        ], [
            'user_id'    => $dto->user->id,
            'blockchain' => $dto->blockchain->value,
            'amount'     => $dto->amount,
            'amount_usd' => $dto->amountUsd,
        ]);
    }

    public function getSummary(Authenticatable $user): Collection
    {
        $hotWallets = HotWallet::selectRaw("
            currency_id AS currency_id,
            sum(amount) as sum_amount,
            count(IF(amount > 0,1,NULL)) as count_with_balance,
            0 as created_count
            ")
            ->where('user_id', $user->id)
            ->groupBy('currency_id')
            ->get();



        $payerAddresses = PayerAddress::selectRaw("
                payer_addresses.currency_id as currency_id,
                count(payer_addresses.id) as created_count"
            )
            ->join('payers','payers.id', '=', 'payer_addresses.payer_id')
            ->join('stores','stores.id', '=', 'payers.store_id')
            ->where('stores.user_id', $user->id)
            ->groupBy('payer_addresses.currency_id')
            ->get()
        ;

        $result = $hotWallets->map(function ($item) use  ($payerAddresses){
            $item->created_count = $payerAddresses->firstWhere('currency_id', $item->currency_id)?->created_count ?? 0;
            return $item;
        });

        return $result;
    }

}
