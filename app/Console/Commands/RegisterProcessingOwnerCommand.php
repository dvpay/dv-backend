<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Processing\Contracts\OwnerContract;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RegisterProcessingOwnerCommand extends Command
{
    protected $signature = 'register:processing:owner {userId=1}';

    protected $description = 'Command description';

    public function handle(OwnerContract $owner): void
    {
        User::unguard();
        $user = User::where('id', $this->argument('userId'))->first();
        $password = Str::random(10);
        $user->update([
            'password' => Hash::make($password),
            'processing_owner_id' =>  $owner->createOwner(config('app.app_domain') . '-user-' . $user->id)
        ]);
        User::reguard();

        $this->info('Login in You cabinet ' . config('app.app_domain') );
        $this->info('You login: ' . $user->email);
        $this->info('You Password: ' . $password);
    }
}
