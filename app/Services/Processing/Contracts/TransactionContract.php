<?php
declare(strict_types=1);

namespace App\Services\Processing\Contracts;

use App\Dto\ProcessingTransactionInfoDto;

interface TransactionContract
{
    /**
     * Get transaction info by tx id
     *
     * @param string $txId
     * @return ProcessingTransactionInfoDto
     */
    public function info(string $txId): ProcessingTransactionInfoDto;

    public function getTransactionByAddress(string $ownerId, string $address): array;

}
