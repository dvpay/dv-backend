<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApiException extends RuntimeException
{
    protected ?int $defaultStatusCode = Response::HTTP_INTERNAL_SERVER_ERROR;

    protected ?string $defaultMessage = 'Something went wrong, please try again!';

    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        if ($code === 0 && $this->defaultStatusCode) {
            $code = $this->defaultStatusCode;
        }

        if ($message === '' && $this->defaultMessage) {
            $message = $this->defaultMessage;
        }

        parent::__construct($message, $code, $previous);
    }
}