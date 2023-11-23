<?php

declare(strict_types=1);

namespace App\Services\Wallet;

use App\Enums\Blockchain;
use App\Models\Currency;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Connection;
use Throwable;

/**
 * WalletCreator
 */
class WalletCreator
{
    /**
     * @param WalletService $walletService
     * @param Connection $db
     */
    public function __construct(
        private readonly WalletService $walletService,
        private readonly Connection    $db
    )
    {
    }

    /**
     * @param User $user
     * @return array
     * @throws Throwable
     */
    public function createWallets(User $user): array
    {
        try {
            $this->db->beginTransaction();

            $wallets = [];

            $blockchains = Blockchain::cases();
            foreach ($blockchains as $blockchain) {
                $currencyExists = Currency::where([
                    ['blockchain', $blockchain],
                    ['has_balance', true],
                ])->exists();

                if (!$currencyExists) {
                    continue;
                }

                $wallet = $this->createWallet($blockchain, $user);
                $this->walletService->createAllBalancesForWallet($wallet);

                $wallets[] = $wallet;
            }

            $this->db->commit();

            return $wallets;
        } catch (Throwable $e) {
            $this->db->rollBack();

            throw $e;
        }
    }

    /**
     * @param Blockchain $blockchain
     * @param User $user
     * @return Wallet
     */
    private function createWallet(Blockchain $blockchain, User $user): Wallet
    {
        $wallet = Wallet::where([
            ['user_id', $user->id],
            ['blockchain', $blockchain],
            ['deleted_at', null],
        ])->first();

        if ($wallet) {
            return $wallet;
        }

        $wallet = Wallet::create([
            'address'    => '',
            'blockchain' => $blockchain,
            'chain'      => $blockchain->getChain(),
            'readonly'   => false,
            'user_id'    => $user->id,
        ]);

        return $wallet;
    }
}