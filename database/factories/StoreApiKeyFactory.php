<?php
declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StoreApiKeyFactory extends Factory
{

    public function definition()
    {
        return [
            'store_id' => $this->faker->uuid,
            'key' => Str::random(40),
            'enabled' => true,
        ];
    }
}