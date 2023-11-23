<?php

namespace App\Jobs;

use App\Dto\Webhook\SendWebhookDto;
use App\Enums\HttpMethod;
use App\Events\WebhookIsSentEvent;
use App\Models\Invoice;
use App\Services\Webhook\WebhookDataService;
use App\Services\Webhook\WebhookManager;
use App\Services\Webhook\WebhookSender;
use GuzzleHttp\Psr7\Response;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;

/**
 * WebhookJob
 */
class WebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 25;

    /**
     * @param Invoice $invoice
     * @param bool $skipCheckHandledWebhook
     * @param bool $sendSuccessWebhook
     */
    public function __construct(
        private readonly Invoice $invoice,
        private readonly bool $skipCheckHandledWebhook = false,
        private readonly bool $sendSuccessWebhook = false
    )
    {
    }

    /**
     * @param WebhookManager $webhookManager
     * @param WebhookDataService $webhookDataService
     * @param WebhookSender $sender
     * @param string $timeout
     * @return void
     */
    public function handle(
        WebhookManager $webhookManager,
        WebhookDataService $webhookDataService,
        WebhookSender $sender,
        string $timeout
    )
    {
        try {
	        if (!$webhooks = $webhookManager->getWebhooks($this->invoice, $this->skipCheckHandledWebhook, $this->sendSuccessWebhook)) {
		        Log::channel('supportLog')->error('Invoice '.$this->invoice->id.' has empty result for method getWebhooks().');

		        return;
	        }

            $request = $webhookDataService->getWebhookData($this->invoice);

            foreach ($webhooks as $webhook) {
                Log::debug('Try send webhook to ' . $webhook->url);
                $dto = new SendWebhookDto([
                    'method' => HttpMethod::POST,
                    'uri' => $webhook->url,
                    'data' => $request,
                    'secret' => $webhook->secret,
                ]);
                $response = $sender->send($dto);
                Log::debug('webhook to ' . $webhook->url . ' is sent');
            }
        } catch (Throwable $e) {
            Log::error("cannot send webhook", [
                'e' => [
                    'msg' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
                'invoice' => [
                    'id' => $this->invoice->id,
                    'status' => $this->invoice->status,
                ]
            ]);

            $response = $e->getMessage();
        }

        $webhook = $webhook ?? null;
        $request = $request ?? [];
        $responseBody = $response instanceof Response ? $response->getBody() : $response;
        $responseCode = $response instanceof Response ? $response->getStatusCode() : HttpResponse::HTTP_INTERNAL_SERVER_ERROR;

        WebhookIsSentEvent::dispatch($this->invoice, $webhook, $request, $responseBody, $responseCode);
        if ($responseCode < 200 || $responseCode >= 300) {
            // If the response code is not okay, release the job to be retried later
            $this->release(60 * pow(2, $this->attempts()));
        }
    }
}
