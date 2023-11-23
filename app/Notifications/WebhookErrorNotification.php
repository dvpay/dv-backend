<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class WebhookErrorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Invoice $invoice,
        private readonly array   $data
    )
    {
        $this->onQueue('notifications');
    }

    public function via($notifiable): array
    {
        return $notifiable->notificationTarget->pluck('slug')->toArray();
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
                ->subject(__('Webhook to store did not send', ['store' => $this->invoice?->store?->name]))
                ->greeting(__('Webhook to store did not send', ['store' => $this->invoice?->store?->name]))
                ->line(__('Invoice') . ': ' . $this->invoice->id)
                ->line(__('Response status code', ['code' => $this->data['code']]))
                ->line(__('Response') . $this->data['response'])
                ->salutation(' ');
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        return TelegramMessage::create()
            ->to($notifiable?->telegram->chat_id)
            ->content(__('Webhook to store did not send', ['store' => $this->invoice?->store?->name]))
            ->line("")
            ->line(__('Invoice') . ': ' . $this->invoice->id)
            ->line(__('Response status code', ['code' => $this->data['code']]))
            ->line(__('Response') . $this->data['response']);
    }
}