<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Invoice;
use App\Models\Webhook;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * WebhookIsSentEvent
 */
class WebhookIsSentEvent
{
    use Dispatchable, InteractsWithQueue, Queueable, InteractsWithSockets, SerializesModels;

    /**
     * @param Invoice $invoice
     * @param Webhook|null $webhook
     * @param array $request
     * @param string $response
     * @param int $responseCode
     */
    public function __construct(
        public readonly Invoice $invoice,
        public readonly ?Webhook $webhook,
        public readonly array $request,
        public readonly string $response,
        public readonly int $responseCode
    )
    {
    }
}