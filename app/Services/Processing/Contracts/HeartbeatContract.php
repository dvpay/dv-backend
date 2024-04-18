<?php

namespace App\Services\Processing\Contracts;

use Psr\Http\Message\ResponseInterface;

interface HeartbeatContract
{
    public function getStatusService(): ResponseInterface;
}