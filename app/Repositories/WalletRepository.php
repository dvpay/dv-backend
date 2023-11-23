<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Dto\Models\WalletDto;
use App\Enums\Blockchain;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Collection;

class WalletRepository
{
    public function getActiveWallet(WalletDto $dto): ?Wallet
    {
        $wallet = Wallet::where([
            ['store_id', $dto->store],
            ['deleted_at', null],
        ]);

        if (isset($dto->blockchain)) {
            $wallet->where('blockchain', $dto->blockchain);
        }

        if (isset($dto->mnemonic)) {
            $wallet->where('seed', $dto->mnemonic);
        }

        if (isset($dto->passPhrase)) {
            $wallet->where('pass_phrase', $dto->passPhrase);
        }

        if (isset($dto->address)) {
            $wallet->where('address', $dto->address);
        }

        return $wallet->first();
    }

    /**
     * @param string $storeId
     * @return Collection<Wallet> | Null
     */
    public function getWalletByStore(string $storeId): ?Collection
    {
        return Wallet::where([
            ['store_id', $storeId],
            ['deleted_at', null],
        ])->get();
    }

    public function getWalletByStoreAndBlockchain(string $storeId, Blockchain $blockchain): ?Wallet
    {
        return Wallet::where([
            ['store_id', $storeId],
            ['blockchain', $blockchain],
            ['deleted_at', null]
        ])->first();
    }

    public function getWalletsByUserId(int $userId): ?Collection
    {
        return Wallet::where('user_id', $userId)->get();
    }
}