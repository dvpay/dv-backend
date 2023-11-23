<?php

declare(strict_types=1);

namespace App\Services\Processing\Fake;

use App\Dto\Transfer\TransferDto;
use App\Enums\Blockchain;
use App\Services\Processing\Contracts\TransferContract;
use Exception;
use GuzzleHttp\Exception\GuzzleException;

class TransferFake implements TransferContract
{
    /**
     * @throws GuzzleException
     */
    public function doTransfer(string $owner, Blockchain $blockchain, bool $isManual, string $address, string $contract = ''): false
    {
        return false;
    }

    /**
     * @throws Exception
     */
    public function transferFromAddress(TransferDto $dto): bool
    {
        if (random_int(0, 1) === 0) {
            throw new Exception('Processing API response with status code: 999');
        }
        return true;
    }

    /**
     * @throws Exception
     */
    public function resendCallback(string $owner, string $address): true
    {
        if (random_int(0, 1) === 0) {
            throw new Exception('Processing API response with status code: 999');
        }
        return true;
    }
}
