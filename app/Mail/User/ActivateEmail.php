<?php

namespace App\Mail\User;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivateEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public User $user;
    public string $token;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, string $token)
    {
        $this->subject = __('Activate your account');
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mail.user.activate')
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject($this->subject)
            ->with([
                'user' => $this->user,
                'token' => $this->token
            ]);
    }
}
