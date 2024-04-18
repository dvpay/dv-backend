<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CurrencyId;
use App\Enums\TransactionType;
use App\Models\Invoice;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    public function definition()
    {
        return [
        'user_id' => User::factory(),
        'store_id' => Store::factory(),
        'invoice_id' => Invoice::factory(),
        'currency_id' => fake()->randomElement(CurrencyId::cases()),
        'tx_id' => fake()->sha1(),
        'type' => fake()->randomElement(TransactionType::cases()),
        'from_address' => fake()->md5(),
        'to_address'=> fake()->md5(),
        'amount' => fake()->randomFloat(8),
        'amount_usd' => fake()->randomFloat(8),
        'rate' => 1,
        'fee' => 0.01,
        'withdrawal_is_manual' => false,
        'network_created_at' => fake()->dateTimeBetween('-1 year'),
        'energy' => rand(1000, 9999999),
        'bandwidth' => rand(1000, 9999999),
        ];

    }
}