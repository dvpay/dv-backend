<?php

declare(strict_types=1);

namespace App\Services\Processing\Contracts;

use App\Dto\Transfer\TransferDto;
use App\Enums\Blockchain;
use Psr\Http\Message\ResponseInterface;

interface TransferContract
{
    public function doTransfer(string $owner, Blockchain $blockchain, bool $isManual, string $address, string $contract = ''): false;
    public function transferFromAddress(TransferDto $dto): bool;

    public function resendCallback(string $owner, string $address): true;
}
