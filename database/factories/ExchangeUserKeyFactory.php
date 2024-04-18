<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Models\ExchangeKey;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExchangeUserKeyFactory extends Factory
{

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'key_id' => ExchangeKey::query()->inRandomOrder()->first(),
            'value' => fake()->md5(),
        ];
    }
}