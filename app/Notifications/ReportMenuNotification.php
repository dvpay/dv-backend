<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Telegram\TelegramMessage;

class ReportMenuNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue('notifications');
    }

    public function via($notifiable): array
    {
        return ['telegram'];
    }

    /**
     * @throws \JsonException
     */
    public function toTelegram($notifiable): TelegramMessage
    {
        return TelegramMessage::create()
                ->to($notifiable->getId())
                ->content(__('Choose for which period you want to receive a report'))
                ->buttonWithCallback(__('Daily Report'), 'dailyReport')
                ->buttonWithCallback(__('Weekly Report'), 'weeklyReport')
                ->buttonWithCallback(__('Monthly Report'), 'monthlyReport');
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
