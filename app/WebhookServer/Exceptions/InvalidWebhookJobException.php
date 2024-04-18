<?php

namespace App\WebhookServer\Exceptions;

use App\WebhookServer\CallWebhookJob;
use Exception;

class InvalidWebhookJobException extends Exception
{
    public static function doesNotExtendCallWebhookJob(string $invalidWebhookJobClass): self
    {
        $callWebhookJob = CallWebhookJob::class;

        return new static("`{$invalidWebhookJobClass}` is not a valid webhook job class because it does not extend `$callWebhookJob`");
    }
}
