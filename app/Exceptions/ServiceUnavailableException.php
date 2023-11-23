<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ServiceUnavailableException extends ApiException
{
    protected ?int $defaultStatusCode = Response::HTTP_SERVICE_UNAVAILABLE;

    protected ?string $defaultMessage = "Service Unavailable";
}
