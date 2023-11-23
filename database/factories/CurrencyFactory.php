<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Blockchain;
use App\Enums\CurrencySymbol;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyFactory extends Factory
{
    public function definition()
    {
        return [
            'id' => CurrencySymbol::USDT->value . '.' . Blockchain::Tron->value,
            'code' => CurrencySymbol::USDT,
            'name' => CurrencySymbol::USDT,
            'is_fiat' => $this->faker->boolean,
            'contract_address' => 'TR7NHqjeKQxGTCi8q8ZY4pL8otSzgjLj6t',
        ];
    }
}