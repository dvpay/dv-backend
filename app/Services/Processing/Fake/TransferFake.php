<?php

declare(strict_types=1);

namespace App\Services\Processing\Fake;

use App\Dto\Transfer\TransferDto;
use App\Enums\Blockchain;
use App\Exceptions\Processing\ResourceException;
use App\Services\Processing\Contracts\TransferContract;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Random\RandomException;

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
            throw new ResourceException();
        }
        return true;
    }

    /**
     * @throws Exception
     */
    public function transferFromProcessing(TransferDto $dto): bool
    {
        if (random_int(0, 1) === 0) {
            throw new ResourceException();
        }
        return true;
    }

    /**
     * @throws Exception
     */
    public function resendCallback(string $owner, string $address, array $transactions): true
    {
        if (random_int(0, 1) === 0) {
            throw new Exception('Processing API response with status code: 999');
        }
        return true;
    }

    /**
     * @throws RandomException
     * @throws Exception
     */
    public function syncTransactions(string $owner, string $address, array $transactions, Blockchain $blockchain): true
    {
        if (random_int(0, 1) === 0) {
            throw new Exception('Processing API response with status code: 999');
        }
        return true;
    }
}
