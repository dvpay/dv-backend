<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\WebhookIsSentEvent;
use App\Services\Telegram\TelegramAdmin;
use Symfony\Component\HttpFoundation\Response;

/**
 * SendWebhookTelegramNotificationListener
 */
class SendWebhookTelegramNotificationListener
{
    /**
     * @param WebhookIsSentEvent $event
     * @return void
     */
    public function handle(WebhookIsSentEvent $event): void
    {
        $invoice = $event->invoice;
        $request = $event->request;
        $response = $event->response;
        $responseCode = $event->responseCode;


        if ($responseCode == Response::HTTP_OK) {
            $invoice->user()->notifyWebhookSuccess($invoice, $request);
        } else {
            $invoice->user()->notifyWebhookError($invoice, ['code' => $responseCode, 'response' => $response]);
        }
    }
}