<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class InviteCreateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $token)
    {
        $this->onQueue('notifications');
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(Lang::get("Invite in :app Team", ['app' => config('app.name')]))
            ->line(Lang::get('Dear :email', ['email' => $notifiable->email]))
            ->line(Lang::get("We\\'re excited to invite you to join our application!"))
            ->line(Lang::get("To get started, please follow the link below to create your account"))
            ->action(Lang::get("Accept invite"), $this->inviteUrl($this->token))
            ->line(Lang::get("Thank you for considering our application. We\\'re looking forward to seeing you online!"));
    }

    protected function inviteUrl($token): string
    {
        $params =  [
            'token' => $token,
        ];

        return 'https://' . config('app.app_domain') . '/invite?' . http_build_query($params);
    }
}