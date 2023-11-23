<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\Telegram\TelegramMessage;

class InvoiceCreationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Invoice $invoice)
    {
        $this->onQueue('notifications');
    }

    public function via($notifiable): array
    {

        return $notifiable->notificationTarget->pluck('slug')->toArray();
    }

    /**
     * @return MailMessage
     */
    public function toMail(): MailMessage
    {
        return (new MailMessage())
                ->subject(__('Invoice created'))
                ->greeting(__('Invoice created'))
                ->line(__('Store name', ['name' => $this->invoice?->store?->name]))
                ->line(__('Invoice Amount',
                        ['amount' => $this->invoice->amount, 'currency' => $this->invoice?->currency_id]))
                ->action(__('View Invoice'), config('setting.payment_form_url').$this->invoice->id)
                ->salutation(' ');
    }

    /**
     * @param $notifiable
     * @return TelegramMessage
     * @throws \JsonException
     */
    public function toTelegram($notifiable)
    {
        return TelegramMessage::create()
                ->to($notifiable?->telegram?->chat_id)
                ->content(__('Invoice created'))
                ->line("")
                ->line(__('Store name', ['name' => $this->invoice?->store?->name]))
                ->line(__('Invoice Amount',
                        ['amount' => $this->invoice->amount, 'currency' => $this->invoice?->currency_id]))
                ->button(__('View Invoice'), config('setting.payment_form_url').$this->invoice->id);
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}