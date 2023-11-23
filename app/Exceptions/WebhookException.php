<?php
declare(strict_types=1);

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\Response;

class WebhookException extends ApiException
{
    protected ?int $defaultStatusCode = Response::HTTP_BAD_REQUEST;

    protected ?string $defaultMessage = "Can't send webhook.";
}