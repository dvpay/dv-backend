<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class DiskFreeSpaceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
    }

    public function via($notifiable): array
    {
        return $notifiable->notificationTarget->pluck('slug')->toArray();
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                ->replyTo($notifiable->email)
                ->subject(__('Disk Space Left'))
                ->greeting(__('Disk Space Left'))
                ->error()
                ->line(__('Disk Space Message'))
                ->salutation(' ');
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        return TelegramMessage::create()
                ->to($notifiable->telegram->chat_id)
                ->line(__('Disk Space Left'))
                ->line(__('Disk Space Message'));
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
