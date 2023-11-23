<?php

namespace App\Mail\User;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeamInviteUserEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public User $invite;
    public User $invited;
    public ?string $password = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $invite, User $invited, string $password = null)
    {
        $this->subject = __('Invite user');
        $this->invite = $invite;
        $this->invited = $invited;
        $this->password = $password;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->view('mail.user.invite_user')
            ->from(config('mail.from.address'), config('mail.from.name'))
            ->subject($this->subject)
            ->with([
                'invite' => $this->invite,
                'invited' => $this->invited,
                'password' => $this->password
            ]);
    }
}
