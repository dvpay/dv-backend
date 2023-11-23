<?php

declare(strict_types=1);

namespace App\Dto;

class ProcessingWalletDto extends ArrayDto
{
    public readonly string $blockchain;
    public readonly string $address;
    public readonly string $balance;
    public readonly string $energyLimit;
    public readonly string $energy;
    public readonly string $bandwidthLimit;
    public readonly string $bandwidth;

}