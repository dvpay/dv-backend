<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class ReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
            private readonly object $data,
            private readonly array  $period,
    ) {
        $this->onQueue('notifications');
    }

    public function via($notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        $telegramMessage = TelegramMessage::create()
                ->to($notifiable->chat_id)
                ->content(__('Merchant. Brief report'))
                ->line("")
                ->line(__('Period').' '.
                        $this->period[0]->format('H:i d.m.y').' '.
                        __('to').' '.
                        $this->period[1]->format('H:i d.m.y'))
                ->line(__('Amount').': '.(int) $this->data->sum.' $')
                ->line(__('Invoice').': '.
                        trans_choice('Invoice Count', $this->data->invoice['count'], [
                                'count' => $this->data->invoice['count'],
                        ]).
                        trans_choice('Payments', $this->data->invoice['paid'], [
                                'paid' => $this->data->invoice['paid']
                        ]))
                ->line(__('Withdrawal').$this->data->sumTransfer)
                ->line(__('Savings on commissions').$this->data->savedOnCommission)
                ->line('');

        foreach ($this->data->storesStat as $store) {
            $telegramMessage->line($store->name.':'.
                    (int) $store->invoices_success_sum_amount.'$ ('.
                    trans_choice('Invoice Count', $store->invoices_success_count, [
                            'count' => $store->invoices_success_count,
                    ]).')'
            );
        }

        return $telegramMessage;
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
