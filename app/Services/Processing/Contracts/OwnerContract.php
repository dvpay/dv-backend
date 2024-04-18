<?php
declare(strict_types=1);

namespace App\Services\Processing\Contracts;

use App\Enums\Blockchain;

interface OwnerContract
{
    /**
     * Creates a new owner of wallets
     *
     * @param string $id
     * @return string
     */
    public function createOwner(string $id): string;

    /**
     * attaches cold wallet to owner with address
     *
     * @param Blockchain $blockchain
     * @param string $address
     * @param string|null $mnemonic
     * @param string $passphrase
     * @return string
     */
    public function attachColdWalletWithAddress(Blockchain $blockchain, string $owner, array $address, ?string $validateCode = null): array;


    public function updateCallbackUrl(string $clientID, string $url): true;

    public function telegramDeepLink(string $owner): array;

}
