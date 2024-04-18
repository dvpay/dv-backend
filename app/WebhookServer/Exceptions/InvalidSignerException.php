<?php

namespace App\WebhookServer\Exceptions;

use App\WebhookServer\Signer\SignerInterface;
use Exception;

class InvalidSignerException extends Exception
{
    public static function doesNotImplementSigner(string $invalidClassName): self
    {
        $signerInterface = SignerInterface::class;

        return new static("`{$invalidClassName}` is not a valid signer class because it does not implement `$signerInterface`");
    }
}
