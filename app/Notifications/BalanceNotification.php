<?php
declare(strict_types=1);

namespace App\Notifications;

use App\Enums\Blockchain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

/**
 *
 */
class BalanceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  object  $data
     */
    public function __construct(private readonly object $data,
    ) {
        $this->onQueue('notifications');
    }

    /**
     * @param $notifiable
     * @return string[]
     */
    public function via($notifiable): array
    {
        return ['telegram'];
    }

    /**
     * @param $notifiable
     * @return TelegramMessage
     */
    public function toTelegram($notifiable): TelegramMessage
    {
        $telegramMessage = TelegramMessage::create()
                ->to($notifiable->chat_id)
                ->line(__('Merchant. Current balances'))
                ->line(__('Received today') . ' ' . $this->data->todaySum . ' (' . __('Yesterday') . $this->data->yesterdaySum . ')')
                ->line(__('Number of payments') . ' ' . $this->data->invoiceCount  . ' (' .$this->data->transactionCount . ' ' . __('PaymentsCount') .')')
                ->line(__('Hot wallet balance') . $this->data->balanceHotWallet->sum('balanceUsd'))
                ->line(__('Processing wallets'));

        foreach ($this->data->processingWallets as $processingWallet) {
            $telegramMessage->line($processingWallet->balance . ' ' . Blockchain::tryFrom($processingWallet->blockchain)->getNativeToken()->value . $processingWallet->blockchain . ' ' . $processingWallet->address);
        }

        $telegramMessage->line(__('Hot wallet balance'));

        foreach ($this->data->balanceHotWallet as  $hotWallet) {
            $telegramMessage->line($hotWallet['balanceUsd'] . '$ ' . $hotWallet['balance'] . '-' . explode('.' , $hotWallet['currency'])[0] . ' (' . $hotWallet['addressCount']['total'] . ')');
        }

        return $telegramMessage;
    }

    /**
     * @param $notifiable
     * @return array
     */
    public function toArray($notifiable): array
    {
        return [];
    }
}
