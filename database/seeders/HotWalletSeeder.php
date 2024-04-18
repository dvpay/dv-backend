<?php

namespace Database\Seeders;

use App\Models\HotWallet;
use App\Models\User;
use Illuminate\Database\Seeder;
use Throwable;

class HotWalletSeeder extends Seeder
{
    /**
     * @throws Throwable
     */
    public function run()
    {
        $user = User::first();

        HotWallet::factory()
            ->count(10)
            ->for($user)
            ->create();
    }
}