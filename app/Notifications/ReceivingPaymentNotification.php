<?php

namespace App\Notifications;

use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class ReceivingPaymentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $explorerLink;

    public function __construct(private readonly Transaction $transaction)
    {
        $this->onQueue('notifications');
        $this->explorerLink = $this->getExplorerUrl($transaction->currency_id, $transaction->tx_id);
    }

    public function via($notifiable): array
    {
        return $notifiable->notificationTarget->pluck('slug')->toArray();
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())
                ->subject(__('Receipt funds'))
                ->greeting(__('Receipt funds'))
                ->line(__('Transaction id', ['txId' => $this->transaction->tx_id]))
                ->line(__('From address', ['address' => $this->transaction->from_address]))
                ->line(__('To address', ['address' => $this->transaction->to_address]))
                ->line(__('Invoice Amount',
                        ['amount' => $this->transaction->amount, 'currency' => $this->transaction->currency_id]))
                ->line(__('Invoice Amount',
                        ['amount' => $this->transaction->amount_usd, 'currency' => $this->transaction->currency_id]))
                ->line(__('Invoice').': '.$this->transaction?->invoice_id)
                ->action(__('Explorer link'), $this->explorerLink)
                ->salutation(' ');
    }

    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
                ->to($notifiable?->telegram->chat_id)
                ->content(__('Receipt funds'))
                ->line("")
                ->line(__('Transaction id', ['txId' => $this->transaction->tx_id]))
                ->line(__('From address', ['address' => $this->transaction->from_address]))
                ->line(__('To address', ['address' => $this->transaction->to_address]))
                ->line(__('Invoice Amount',
                        ['amount' => $this->transaction->amount, 'currency' => $this->transaction->currency_id]))
                ->line(__('Invoice Amount',
                        ['amount' => $this->transaction->amount_usd, 'currency' => $this->transaction->currency_id]))
                ->line(__('Invoice').': '.$this->transaction?->invoice_id)
                ->button(__('Explorer link'), $this->explorerLink);
    }

    public function toArray($notifiable): array
    {
        return [];
    }

    private function getExplorerUrl(string $currencyId, string $tx): string
    {
        $currency = Currency::find($currencyId);

        return $currency->blockchain->getExplorerUrl().'/'.$tx;
    }
}