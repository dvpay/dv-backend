<?php
declare(strict_types=1);

namespace App\Exceptions;


use Symfony\Component\HttpFoundation\Response;

class RateNotFoundException extends ApiException
{
    protected ?int $defaultStatusCode = Response::HTTP_NOT_FOUND;

    protected ?string $defaultMessage = "Currency rate was not found.";
}