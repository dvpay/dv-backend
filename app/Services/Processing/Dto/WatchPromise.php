<?php
declare(strict_types=1);

namespace App\Services\Processing\Dto;

use DateTime;

class WatchPromise
{
    public function __construct(
        public readonly string   $address,
        public readonly string   $watchId,
        public readonly DateTime $expiredAt
    )
    {
    }
}