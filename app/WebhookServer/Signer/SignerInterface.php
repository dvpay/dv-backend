<?php

namespace App\WebhookServer\Signer;

interface SignerInterface
{
    public function signatureHeaderName(): string;
    public function calculateSignature(string $webhookUrl, array $payload, string $secret): string;
}