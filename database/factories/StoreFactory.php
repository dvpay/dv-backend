<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CurrencySymbol;
use App\Enums\RateSource;
use App\Models\User;
use App\Services\Processing\Contracts\OwnerContract;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::factory()->create()->id,
            'name' => $this->faker->name,
            'site' => $this->faker->url,
            'currency_id' => CurrencySymbol::USD->value,
            'rate_source' => RateSource::LoadRateFake,
            'invoice_expiration_time' => config('setting.invoice_lifetime'),
            'processing_owner_id' => app(OwnerContract::class)->createOwner('test-store-' . $this->faker->uuid),
        ];
    }
}