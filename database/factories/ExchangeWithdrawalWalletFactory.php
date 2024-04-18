<?php

namespace Database\Factories;

use App\Enums\CurrencySymbol;
use App\Enums\ExchangeChainType;
use App\Models\Exchange;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ExchangeWithdrawalWallet>
 */
class ExchangeWithdrawalWalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'address' => fake()->md5(),
            'is_withdrawal_enable' => true,
            'min_balance' => 5,
            'chain' => fake()->randomElement(ExchangeChainType::cases()),
            'currency' => (fake()->randomElement(CurrencySymbol::cases())->toLower()),
            'user_id' => User::factory(),
            'exchange_id' => Exchange::inRandomOrder()->first(),
        ];
    }


}
