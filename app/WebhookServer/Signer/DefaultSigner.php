<?php

namespace App\WebhookServer\Signer;

class DefaultSigner implements SignerInterface
{

    public function signatureHeaderName(): string
    {
        return config('webhook-server.signature_header_name');
    }

    public function calculateSignature(string $webhookUrl, array $payload, string $secret): string
    {
        return hash('sha256', json_encode($payload) . $secret);
    }
}