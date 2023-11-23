<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RootUserSeeder extends Seeder
{
    public function run()
    {
        $user = User::firstOrCreate(['email' => 'admin@admin.com'], [
                'name'                => 'admin',
                'email'               => 'admin@admin.com',
                'password'            => Hash::make('password'),
                'email_verified_at'   => now(),
                'processing_owner_id' => Str::random(20),
        ]);

        $user->assignRole([UserRole::Root->value, UserRole::Admin->value]);
    }
}
