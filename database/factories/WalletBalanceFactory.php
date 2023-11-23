<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Blockchain;
use App\Enums\CurrencySymbol;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletBalanceFactory extends Factory
{

    public function definition()
    {
        return [
            'wallet_id' => $this->faker->uuid,
            'currency_id' => CurrencySymbol::USDT->value . '.' . Blockchain::Tron->value,
            'balance' => 0,
        ];
    }
}