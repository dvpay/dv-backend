<?php

namespace Database\Factories;

use App\Enums\CurrencyId;
use App\Models\Payer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PayerAddress>
 */
class PayerAddressFactory extends Factory
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
            'payer_id' => Payer::factory(),
            'currency_id' => $currency->value,
            'blockchain' => $currency->getBlockchain(),
            'address' => fake()->md5(),
        ];
    }


}
