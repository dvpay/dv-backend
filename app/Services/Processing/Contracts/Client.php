<?php

declare(strict_types=1);

namespace App\Services\Processing\Contracts;

use App\Enums\HttpMethod;
use Psr\Http\Message\ResponseInterface;

interface Client
{
    /**
     * @param HttpMethod $method
     * @param string $uri
     * @param array $data
     *
     * @return mixed
     */
    public function request(HttpMethod $method, string $uri, array $data): ResponseInterface;
}
