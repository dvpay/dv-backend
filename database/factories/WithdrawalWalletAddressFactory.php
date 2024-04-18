<?php

namespace Database\Factories;

use App\Enums\CurrencySymbol;
use App\Enums\ExchangeChainType;
use App\Enums\WithdrawalInterval;
use App\Enums\WithdrawalRuleType;
use App\Models\Exchange;
use App\Models\User;
use App\Models\WithdrawalWallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WithdrawalWalletAddress>
 */
class WithdrawalWalletAddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'withdrawal_wallet_id' => WithdrawalWallet::factory(),
            'address' => fake()->md5(),

        ];
    }


}
