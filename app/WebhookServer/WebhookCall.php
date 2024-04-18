<?php

namespace App\WebhookServer;

use App\WebhookServer\Events\DispatchingWebhookCallEvent;
use App\WebhookServer\Exceptions\CouldNotCallWebhookException;
use App\WebhookServer\Exceptions\InvalidSignerException;
use App\WebhookServer\Exceptions\InvalidWebhookJobException;
use App\WebhookServer\Signer\DefaultSigner;
use App\WebhookServer\Signer\SignerInterface;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Support\Str;

class WebhookCall
{
    protected CallWebhookJob $callWebhookJob;
    protected string $uuid = '';

    protected string $secret;

    protected SignerInterface $signer;

    protected array $headers = [];

    private array $payload = [];

    private int $delay = 0;
    private bool $signWebhook = true;

    /**
     * @throws InvalidWebhookJobException
     * @throws InvalidSignerException
     */
    public static function create(): self
    {
        return (new static())
            ->useJob(CallWebhookJob::class)
            ->uuid(Str::uuid())
            ->onQueue(config('webhook-server.queue'))
            ->onConnection(config('webhook-server.connection') ?? null)
            ->useHttpVerb(config('webhook-server.http_verb'))
            ->maximumTries(config('webhook-server.tries'))
            ->timeoutInSeconds(config('webhook-server.timeout_in_seconds'))
            ->signUsing(DefaultSigner::class)
            ->withHeaders(config('webhook-server.headers'));
    }

    /**
     * @throws InvalidWebhookJobException
     */
    public function useJob(string $webhookJobClass): self
    {
        $job = app($webhookJobClass);

        if (!$job instanceof CallWebhookJob) {
            throw InvalidWebhookJobException::doesNotExtendCallWebhookJob($webhookJobClass);
        }
        $this->callWebhookJob = $job;
        return $this;
    }

    public function url(string $url): self
    {
        $this->callWebhookJob->webhookUrl = $url;

        return $this;
    }

    public function uuid(string $uuid): self
    {
        $this->uuid = $uuid;
        $this->callWebhookJob->uuid = $uuid;
        return $this;
    }

    public function onQueue(?string $queue): self
    {
        $this->callWebhookJob->queue = $queue;
        return $this;
    }

    public function onConnection(?string $connection): self
    {
        $this->callWebhookJob->connection = $connection;
        return $this;
    }

    public function useHttpVerb(string $verb): self
    {
        $this->callWebhookJob->httpVerb = $verb;
        return $this;
    }

    public function maximumTries(int $tries): self
    {
        $this->callWebhookJob->tries = $tries;
        return $this;
    }

    public function timeoutInSeconds(int $timeoutInSeconds): self
    {
        $this->callWebhookJob->requestTimeout = $timeoutInSeconds;
        return $this;
    }

    /**
     * @throws InvalidSignerException
     */
    public function signUsing(string $signerClass): self
    {
        if (!is_subclass_of($signerClass, SignerInterface::class)) {
            throw InvalidSignerException::doesNotImplementSigner($signerClass);
        }
        $this->signer = app($signerClass);
        return $this;
    }

    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function payload(array $payload): self
    {
        $this->payload = $payload;
        $this->callWebhookJob->payload = $payload;
        return $this;
    }

    public function doNotSign(): self
    {
        $this->signWebhook = false;
        return $this;
    }

    public function useSecret(string $secret): self
    {
        $this->secret = $secret;
        return $this;
    }

    public function sendRawBody(string $body): self
    {
        $this->callWebhookJob->payload = $body;
        $this->callWebhookJob->outputType = "RAW";

        return $this;
    }

    /**
     * @throws CouldNotCallWebhookException
     */
    public function dispatch(): PendingDispatch
    {
        $this->prepareForDispatch();

        event(new DispatchingWebhookCallEvent(
            $this->callWebhookJob->httpVerb,
            $this->callWebhookJob->webhookUrl,
            $this->callWebhookJob->payload,
            $this->callWebhookJob->headers,
            $this->callWebhookJob->uuid,
        ));

        return dispatch($this->callWebhookJob)
            ->delay($this->delay);
    }

    /**
     * @throws CouldNotCallWebhookException
     */
    public function dispatchIf($condition): PendingDispatch|null
    {
        if ($condition) {
            return $this->dispatch();
        }

        return null;
    }

    /**
     * @throws CouldNotCallWebhookException
     */
    public function dispatchUnless($condition): PendingDispatch|null
    {
        return $this->dispatchIf(!$condition);
    }

    /**
     * @throws CouldNotCallWebhookException
     */
    public function dispatchSync(): void
    {
        $this->prepareForDispatch();

        dispatch_sync($this->callWebhookJob);
    }

    /**
     * @throws CouldNotCallWebhookException
     */
    public function dispatchSyncIf($condition): void
    {
        if ($condition) {
            $this->dispatchSync();
        }
    }

    /**
     * @throws CouldNotCallWebhookException
     */
    public function dispatchSyncUnless($condition): void
    {
        $this->dispatchSyncIf(!$condition);
    }

    public function meta(array $meta): self
    {
        $this->callWebhookJob->meta = $meta;
        return $this;
    }

    public function delay(int $delay): self
    {
        $this->delay = $delay;
        return $this;
    }

    public function attempts(int $attempts): self
    {
        $this->callWebhookJob->attempts = $attempts;
        return $this;
    }

    /**
     * @throws CouldNotCallWebhookException
     */
    protected function prepareForDispatch(): void
    {
        if (!$this->callWebhookJob->webhookUrl) {
            throw CouldNotCallWebhookException::urlNotSet();
        }

        if ($this->signWebhook && empty($this->secret)) {
            throw CouldNotCallWebhookException::secretNotSet();
        }

        $this->callWebhookJob->headers = $this->getAllHeaders();
    }


    protected function getAllHeaders(): array
    {
        $headers = $this->headers;

        if (!$this->signWebhook) {
            return $headers;
        }

        $signature = $this->signer->calculateSignature($this->callWebhookJob->webhookUrl, $this->payload, $this->secret);
        $headers[$this->signer->signatureHeaderName()] = $signature;

        return $headers;
    }
}