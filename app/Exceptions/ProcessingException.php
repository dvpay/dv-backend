<?php
declare(strict_types=1);

namespace App\Exceptions;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

class ProcessingException extends RuntimeException
{
    public function __construct(string $message = "", ?ResponseInterface $response = null, ?Throwable $previous = null)
    {
        $code = 0;
        if ($response) {
            $code = $response->getStatusCode();
            $message .= sprintf('. Status code: %d, body: %s', $code, $response->getBody());
        }

        parent::__construct($message, $code, $previous);
    }
}