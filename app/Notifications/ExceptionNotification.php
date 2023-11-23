<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class ExceptionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected readonly string $message)
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
                ->replyTo($notifiable->email)
                ->subject(__('Caught Error'))
                ->greeting(__('Caught Error'))
                ->error()
                ->line($this->message)
                ->salutation(' ');
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        return TelegramMessage::create()
                ->to($notifiable?->telegram->chat_id)
                ->content(__('Caught Error'))
                ->line('')
                ->escapedLine($this->message);
    }
}
