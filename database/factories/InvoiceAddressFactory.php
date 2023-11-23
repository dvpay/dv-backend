<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Blockchain;
use App\Enums\CurrencySymbol;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvoiceAddressFactory extends Factory
{

    public function definition()
    {
        return [
            'invoice_id' => $this->faker->uuid,
            'address' => Str::random(21),
            'blockchain' => Blockchain::Tron,
            'watch_id' => $this->faker->uuid,
            'currency_id' => CurrencySymbol::USDT->value . '.' . Blockchain::Tron->value,
            'balance' => 0,
            'rate' => 1,
            'invoice_currency_id' => CurrencySymbol::USD->value,
        ];
    }
}