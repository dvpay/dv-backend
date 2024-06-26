<?php

namespace App\Notifications;

use App\Enums\Queue;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class TransferNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $explorerLink;

    public function __construct(private readonly Transaction $transaction)
    {
        $this->onQueue(Queue::Notifications->value);
        $this->explorerLink = $this->getExplorerUrl($transaction->currency_id, $transaction->tx_id);
    }

    public function via($notifiable): array
    {
        return $notifiable->notificationTarget->pluck('slug')->toArray();
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
                ->subject('*' . __('Transfer') . '*')
                ->greeting('*' . __('Transfer') . '*')
                ->line(__('Transaction id: :txId', ['txId' => $this->transaction->tx_id]))
                ->line(__('From address: :address', ['address' => $this->transaction->from_address]))
                ->line(__('To address: :address', ['address' => $this->transaction->to_address]))
                ->line(__('Amount: *:amount* :currency',
                        ['amount' => $this->transaction->amount, 'currency' => $this->transaction->currency_id]))
                ->line(__('Amount: *:amount* :currency',
                        ['amount' => $this->transaction->amount_usd, 'currency' => $this->transaction->currency_id]))
                ->action(__('Explorer link'), $this->explorerLink)
                ->salutation(' ');
    }

    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
                ->to($notifiable?->telegram?->chat_id)
                ->content('*' . __('Transfer') . '*')
                ->line("")
                ->line(__('Transaction id: :txId', ['txId' => $this->transaction->tx_id]))
                ->line(__('From address: :address', ['address' => $this->transaction->from_address]))
                ->line(__('To address: :address', ['address' => $this->transaction->to_address]))
                ->line(__('Amount: *:amount* :currency',
                        ['amount' => $this->transaction->amount, 'currency' => $this->transaction->currency_id]))
                ->line(__('Amount: *:amount* :currency',
                        ['amount' => $this->transaction->amount_usd, 'currency' => $this->transaction->currency_id]))
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