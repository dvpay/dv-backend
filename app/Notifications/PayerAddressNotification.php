<?php

namespace App\Notifications;

use App\Models\Notification\NotificationLog;
use App\Models\PayerAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayerAddressNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param array<PayerAddress> $payerAddresses
     * @param string|null $ip
     */
    public function __construct(
        protected readonly array   $payerAddresses,
        protected readonly ?string $ip = ''
    )
    {
        $this->onQueue('notifications');
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $id = '';

        $mail = (new MailMessage)
            ->subject(__('You Addresses'))
            ->greeting(__('You Addresses'));

        foreach ($this->payerAddresses as $payerAddress) {
            $id .= substr(md5($payerAddress->address), 1, 6);
            $mail->line('Your top-up address on network: ' . $payerAddress->blockchain->name)
                ->line('Address: ' . $payerAddress->address);
        }

        $mail->line('Email ID: ' . $id);

        if ($this->ip) {
            $mail->line('Wallet address requested from IP: ' . $this->ip);
        }

        $mail->line('We kindly ask you to pay attention to the coincidence of characters when entering the wallet when transferring. If the wallet is entered incorrectly at the time of payment, no refund will be made.')
            ->line('')
            ->line('If the address on the site and in the email are different, then contact support')
            ->line('Do not delete this letter, in case of problems it is necessary for investigation.')
            ->line('Thank you for using our application!');

        NotificationLog::create([
            'email'          => $notifiable->routes['mail'],
            'text'           => $mail->render(),
            'model_type'     => 'App\Notifications\PayerAddressNotification',
            'model_variable' => $this->payerAddresses,
        ]);

        return $mail;
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
