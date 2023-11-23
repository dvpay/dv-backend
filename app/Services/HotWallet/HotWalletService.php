<?php

namespace App\Services\HotWallet;

use App\Dto\HotWallet\HotWalletDto;
use App\Dto\HotWallet\HotWalletsListDto;
use App\Models\HotWallet;

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
            'state'      => $dto->state->value,
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
            'state'      => $dto->state->value,
            'amount'     => $dto->amount,
            'amount_usd' => $dto->amountUsd,
        ]);
    }
}
