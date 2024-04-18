<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;


class CreateRootUserAuthLinkCommand extends Command
{
    protected $signature = 'auth:root-link';

    protected $description = 'Create Root User Auth Link';

    public function handle(): void
    {

        $user = User::query()
            ->role(UserRole::Root->value)
            ->first();

        if(!$user) {
            $this->warn('There is no Root user. Trying to find admin.');

            $user = User::query()
                ->role(UserRole::Admin->value)
                ->first();
        }

        if(!$user) {
            $this->error('There is no Root or Admin users.');
            return;
        }

        $expiresAt = Carbon::now()->addMinutes(5);

        $token = $user->createToken(
            name: 'console-root-token',
            expiresAt: $expiresAt
        );

        $this->info('Here is your link:');
        $this->info('https://' . config('app.app_domain') . '/login-by-token/' . $token->plainTextToken);
        $this->info('Token will expire at: ' . $expiresAt->format("Y-m-d H:i:s") . ' (UTC)');
    }

}
