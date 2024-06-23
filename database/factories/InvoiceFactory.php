<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CurrencySymbol;
use App\Enums\InvoiceStatus;
use App\Models\Currency;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class InvoiceFactory extends Factory
{
    public function definition()
    {
        return [
            'status' => InvoiceStatus::Waiting,
            'store_id' => Store::factory(),
            'order_id' => Str::random(10),
            'currency_id' => CurrencySymbol::USD->value,
            'amount' => 100.1,
            'description' => $this->faker->text,
            'return_url' => $this->faker->url,
            'success_url' => $this->faker->url,
            'expired_at' => Carbon::now()->addDays(30)->format('Y-m-d H:i:s'),
        ];
    }
}
