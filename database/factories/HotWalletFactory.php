<?php

namespace Database\Factories;

use App\Enums\CurrencyId;
use App\Enums\HotWalletState;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HotWallet>
 */
class HotWalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {

        /* @var CurrencyId $currency */
        $currency = fake()->randomElement(CurrencyId::cases());

        return [
            'currency_id' => $currency->value,
            'user_id' => User::factory(),
            'address' => fake()->md5(),
            'blockchain' => $currency->getBlockchain(),
            'amount' => fake()->randomFloat(8),
            'amount_usd' => fake()->randomFloat(8),
        ];


    }

}
