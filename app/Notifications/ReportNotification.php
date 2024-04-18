<?php

namespace App\Notifications;

use App\Enums\Queue;
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
        $this->onQueue(Queue::Notifications->value);
    }

    public function via($notifiable): array
    {
        return ['telegram'];
    }

    public function toTelegram($notifiable): TelegramMessage
    {
        $telegramMessage = TelegramMessage::create()
                ->to($notifiable->chat_id)
                ->content('*' . __('Merchant. Brief report') . '*')
                ->line("")
                ->line(__('Period').': '.
                        $this->period[0]->format('H:i d.m.y').' '.
                        __('to').' '.
                        $this->period[1]->format('H:i d.m.y'))
                ->line(__('Amount').': '.(int) $this->data->sum.' $')
/*
  "Invoice Count": "{1} :count Invoice |[2,4] :count Invoices |[5,*] :count Invoices ",
  "Payments": "{1} и :paid Payments|[2,4] и :paid Payments|[0,5,*] и :paid Payments",
 */
//                ->line(__('Invoice').': '.
//                        trans_choice('Invoice Count', $this->data->invoice['count'], [
//                                'count' => $this->data->invoice['count'],
//                        ]).
//                        trans_choice('Payments', $this->data->invoice['paid'], [
//                                'paid' => $this->data->invoice['paid']
//                        ]))
                ->line(
                    __('Invoice count') . ': ' . $this->data->invoice['count'] .
                    __('Payments') . ': ' . $this->data->invoice['paid']
                    )
                ->line(__('Withdrawal') . ':' . $this->data->sumTransfer)
                ->line(__('Savings on commissions') . ':' . $this->data->savedOnCommission)
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
