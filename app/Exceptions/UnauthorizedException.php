<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class UnauthorizedException extends ApiException
{
    protected ?int $defaultStatusCode = Response::HTTP_UNAUTHORIZED;

    protected ?string $defaultMessage = "You don't have permission to this action!";
}