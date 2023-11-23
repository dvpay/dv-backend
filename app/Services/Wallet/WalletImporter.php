<?php

declare(strict_types=1);

namespace App\Services\Wallet;

use App\Dto\Models\WalletDto;
use App\Models\Wallet;
use App\Repositories\WalletRepository;
use App\Services\Processing\Contracts\OwnerContract;
use Illuminate\Database\Connection;
use Throwable;

class WalletImporter
{
    public function __construct(
        private readonly OwnerContract    $ownerService,
        private readonly WalletRepository $walletRepository,
        private readonly WalletService    $walletService,
        private readonly Connection       $db
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function import(WalletDto $dto): Wallet
    {
        try {
            $this->db->beginTransaction();

            if ($wallet = $this->walletRepository->getActiveWallet($dto)) {
                return $wallet;
            }

            $wallet = $this->walletRepository->getWalletByStoreAndBlockchain($dto->store->id, $dto->blockchain);
            $wallet?->delete();

            $wallet = $this->createWallet($dto);
            $this->walletService->createAllBalancesForWallet($wallet);

            $this->db->commit();

            return $wallet;
        } catch (Throwable $e) {
            $this->db->rollBack();

            throw $e;
        }
    }

    private function createWallet(WalletDto $dto): Wallet
    {
        if (isset($dto->address)) {
            $address = $this->ownerService->attachColdWalletWithAddress($dto->blockchain, $dto->store->processing_owner_id, $dto->address);
            $readonly = true;
        } else {
            $address = $this->ownerService->attachHotWalletWithMnemonic($dto->blockchain, $dto->store->processing_owner_id, $dto->mnemonic, $dto->passPhrase ?? '');
            $readonly = false;
        }

        $wallet = new Wallet([
            'address' => $address,
            'blockchain' => $dto->blockchain,
            'store_id' => $dto->store->id,
            'readonly' => $readonly,
            'seed' => $dto->mnemonic ?? null,
            'pass_phrase' => $dto->passPhrase ?? null,
        ]);

        $wallet->save();

        return $wallet;
    }
}