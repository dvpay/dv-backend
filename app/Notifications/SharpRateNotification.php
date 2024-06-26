<?php

namespace App\Notifications;

use App\Enums\Queue;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class SharpRateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly array $data)
    {
        $this->onQueue(Queue::Notifications->value);
    }

    public function via($notifiable): array
    {
        return $notifiable->notificationTarget->pluck('slug')->toArray();
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())
                ->subject(__('Sharp Exchange Rate Change'))
                ->greeting(__('Sharp Exchange Rate Change'))
                ->line(__('Currency from: :symbol', ['symbol' => $this->data['from']]))
                ->line(__('Currency to: :symbol', ['symbol' => $this->data['to']]))
                ->line(__('Old rate :amount', ['amount' => $this->data['oldRate']]))
                ->line(__('Current rate :amount', ['amount' => $this->data['currentRate']]))
                ->line(__('Difference :amount', ['amount' => $this->data['difference']]))
                ->salutation(' ');
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        return TelegramMessage::create()
                ->to($notifiable?->telegram->chat_id)
                ->content(__('Sharp Exchange Rate Change'))
                ->line("")
                ->line(__('Currency from: :symbol', ['symbol' => $this->data['from']]))
                ->line(__('Currency to: :symbol', ['symbol' => $this->data['to']]))
                ->line(__('Old rate :amount', ['amount' => $this->data['oldRate']]))
                ->line(__('Current rate :amount', ['amount' => $this->data['currentRate']]))
                ->line(__('Difference :amount', ['amount' => $this->data['difference']]));
    }

}