<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class WebhookSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
            private readonly Invoice $invoice,
            private readonly array   $data
    ) {
        $this->onQueue('notifications');
    }

    public function via($notifiable): array
    {
        return $notifiable->notificationTarget->pluck('slug')->toArray();
    }

    public function toMail(): MailMessage
    {
        $currencyCode = $this->invoice->currency->code->value;

        $mailMessage = (new MailMessage())
                ->subject(__('Webhook to store is sent', ['store' => $this->invoice->store->name]))
                ->line("")
                ->line(__('Status').': '.$this->invoice->status->value)
                ->line(__('Invoice Amount', ['amount' => $this->invoice->amount, 'currency' => $currencyCode]))
                ->line(__('Received Amount', ['amount' => $this->data['receivedAmount'], 'currency' => $currencyCode]))
                ->line(__('Transactions'));

        foreach ($this->data['transactions'] as $transaction) {
            $mailMessage->line($transaction->txId.': '.$transaction['currency']);
        }

        $mailMessage->salutation(' ');

        return $mailMessage;
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        $currencyCode = $this->invoice->currency->code->value;
        $telegramMessage = TelegramMessage::create()
                ->to($notifiable?->telegram->chat_id)
                ->content(__('Webhook to store is sent', ['store' => $this->invoice->store->name]))
                ->line("")
                ->line(__('Status').': '.$this->invoice->status->value)
                ->line(__('Invoice Amount', ['amount' => $this->invoice->amount, 'currency' => $currencyCode]))
                ->line(__('Received Amount', ['amount' => $this->data['receivedAmount'], 'currency' => $currencyCode]))
                ->line(__('Transactions'));

        foreach ($this->data['transactions'] as $transaction) {
            $telegramMessage->line($transaction->txId.': '.$transaction['currency']);
        }

        return $telegramMessage;
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}