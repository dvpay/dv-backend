<?php

namespace Database\Factories;

use App\Enums\WebhookType;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payer>
 */
class WebhookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'store_id' => Store::factory(),
            'url' => $this->faker->url,
            'secret' => $this->faker->word,
            'enabled' => true,
            'events' => [
                WebhookType::InvoiceCreated,
                WebhookType::UnconfirmedTransaction,
                WebhookType::InvoiceExpired,
                WebhookType::PaymentReceived,
            ]
        ];
    }

}
