<?php
declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RateSourceFactory extends Factory
{

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'uri' => $this->faker->url,
        ];
    }
}