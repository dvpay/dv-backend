<?php

declare(strict_types=1);

namespace App\Services\Processing\Contracts;

use App\Dto\Transfer\TransferDto;
use App\Enums\Blockchain;
use JetBrains\PhpStorm\Deprecated;


interface TransferContract
{
    #[Deprecated] // app/Services/Processing/ProcessingService.php :: L:158
    public function doTransfer(string $owner, Blockchain $blockchain, bool $isManual, string $address, string $contract = ''): false;
    public function transferFromAddress(TransferDto $dto): bool;
    public function transferFromProcessing(TransferDto $dto): bool;

    public function resendCallback(string $owner, string $address, array $transactions): true;

    public function syncTransactions(string $owner, string $address, array $transactions, Blockchain $blockchain): true;

}
