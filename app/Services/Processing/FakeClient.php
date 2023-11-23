<?php

namespace App\Services\Processing;

use App\Enums\HttpMethod;
use App\Services\Processing\Contracts\Client;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class FakeClient implements Client
{
    public function request(HttpMethod $method, string $uri, array $data): ResponseInterface
    {
        return new Response();
    }
}