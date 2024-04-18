<?php

namespace Database\Factories;

use App\Enums\CurrencySymbol;
use App\Enums\ExchangeChainType;
use App\Enums\WithdrawalInterval;
use App\Enums\WithdrawalRuleType;
use App\Models\Exchange;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WithdrawalWallet>
 */
class WithdrawalWalletFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $chainType = fake()->randomElement(ExchangeChainType::cases());

        return [
            'user_id' => User::factory(),
            'exchange_id' => Exchange::inRandomOrder()->first(),
            'chain' => $chainType,
            'blockchain' => $chainType->blockchain(),
            'currency' => (fake()->randomElement(CurrencySymbol::cases())->toLower()),
            'type' => WithdrawalRuleType::Manual,
            'withdrawal_enabled' => true,
            'withdrawal_min_balance' => 0,
            'withdrawal_interval' => WithdrawalInterval::EveryOneMin,

        ];
    }


}
