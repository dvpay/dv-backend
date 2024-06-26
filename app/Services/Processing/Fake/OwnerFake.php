<?php
declare(strict_types=1);

namespace App\Services\Processing\Fake;

use App\Enums\Blockchain;
use App\Exceptions\ApiException;
use App\Services\Processing\Contracts\OwnerContract;

readonly class OwnerFake implements OwnerContract
{
    public function __construct(private AddressFake $addressFake)
    {
    }

    public function createOwner(string $id): string
    {
        return fake()->uuid();
    }

    public function attachColdWalletWithAddress(Blockchain $blockchain, string $owner, array $address, ?string $validateCode = null): array
    {

        if (isset($validateCode) && $validateCode !== '111111') {
            throw new ApiException('Invalid code', 400);
        }
        return $address;
    }

    public function attachHotWalletWithMnemonic(Blockchain $blockchain, string $owner, string $mnemonic, string $passphrase): string
    {
        return $this->addressFake->generate($blockchain, $owner);
    }

    public function attachHotWalletWithPrivateKey(Blockchain $blockchain, string $owner, string $privateKey): string
    {
        return $this->addressFake->generate($blockchain, $owner);
    }

    public function updateCallbackUrl(string $clientID, string $url): true
    {
        return true;
    }

    public function telegramDeepLink(string $owner): array
    {
        return [
            "owner" => $owner,
			"deeplink" =>  'tglink.com',
			"hasChatID" => true,
        ];
    }
}
