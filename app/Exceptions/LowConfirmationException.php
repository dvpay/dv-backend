<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LowConfirmationException extends ApiException
{
    protected ?int $defaultStatusCode = Response::HTTP_UNPROCESSABLE_ENTITY;

    protected ?string $defaultMessage = 'Low Confirmation';

}