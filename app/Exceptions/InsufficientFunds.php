<?php

declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class InsufficientFunds extends ApiException
{
    protected ?int $defaultStatusCode = Response::HTTP_BAD_REQUEST;

    protected ?string $defaultMessage = "Insufficient funds.";
}