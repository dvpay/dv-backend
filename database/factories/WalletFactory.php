<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Blockchain;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{

    public function definition()
    {
        return [
            'address' => 'asdASdasdasdASdfEsdad',
            'blockchain' => Blockchain::Tron,
//            'store_id' => $this->faker->uuid,
            'readonly' => false,
            'chain' => Blockchain::Tron->getChain(),
            'seed' => 'SAdaDASdas asd asdawdasd asdasdasd asd',
            'pass_phrase' => 'Pass',
        ];
    }
}