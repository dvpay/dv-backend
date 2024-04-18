<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CurrencyId;
use App\Enums\TransactionType;
use App\Models\Invoice;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnconfirmedTransactionFactory extends Factory
{
    public function definition()
    {

        return [
            'user_id'      => User::factory(),
            'store_id'     => Store::factory(),
            'invoice_id'   => Invoice::factory(),
            'from_address' => fake()->md5(),
            'to_address'   => fake()->md5(),
            'tx_id'        => fake()->sha1(),
            'currency_id'  => fake()->randomElement(CurrencyId::cases()),
            'amount'       => rand(1, 100),
            'amount_usd'   => rand(1, 100)
        ];

    }
}