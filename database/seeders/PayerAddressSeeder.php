<?php

namespace Database\Seeders;

use App\Models\Payer;
use App\Models\PayerAddress;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Throwable;

class PayerAddressSeeder extends Seeder
{
    /**
     * @throws Throwable
     */
    public function run()
    {
        $user = User::first();

        $store = Store::factory()
            ->for($user)
            ->create();

        PayerAddress::factory()
            ->for(Payer::factory()
                ->for($store)
            )
            ->create();

    }
}