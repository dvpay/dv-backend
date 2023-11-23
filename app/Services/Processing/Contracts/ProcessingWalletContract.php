<?php

declare(strict_types=1);

namespace App\Services\Processing\Contracts;

interface ProcessingWalletContract
{
    /**
     * @param string $ownerId
     * @return array
     */
    public function getWallets(string $ownerId): array;
}
