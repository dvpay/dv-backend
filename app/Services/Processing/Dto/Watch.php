<?php
declare(strict_types=1);

namespace App\Services\Processing\Dto;

use App\Enums\Blockchain;
use DateInterval;
use DateTime;

class Watch
{
    /**
     * @param Blockchain $blockchain
     * @param string $owner processing owner, stored in stores table
     * @param string $address address for watch should be
     * @param int $duration in seconds
     * @param string $contractAddress contract for watch
     * @param string $amount concrete amount for watch
     * @param string $destination user wallet address
     */
    public function __construct(
        public readonly Blockchain $blockchain,
        public readonly string $owner,
        public string $address = '',
        public readonly int $duration = 0,
        public readonly string $contractAddress = '',
        public readonly string $amount = '',
        public readonly string $destination = '',
    )
    {
    }

    public function withAddress(string $address): static
    {
        return new static(
            $this->blockchain,
            $this->owner,
            $address,
            $this->duration,
            $this->contractAddress,
            $this->amount,
            $this->destination,
        );
    }
}