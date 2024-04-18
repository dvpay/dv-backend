<?php

namespace Tests\Feature\Invoice;

 use App\Enums\Blockchain;
 use App\Enums\CurrencySymbol;
 use App\Enums\UserRole;
 use App\Models\Currency;
 use App\Models\Invoice;
 use App\Models\InvoiceAddress;
 use App\Models\Store;
 use App\Models\StoreApiKey;
 use App\Models\User;
 use App\Models\Wallet;
 use App\Models\WalletBalance;
 use Database\Seeders\RoleSeeder;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Testing\Fluent\AssertableJson;
 use Symfony\Component\HttpFoundation\Response;
 use Tests\TestCase;

class GetInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function testGetInvoiceInfoStatusOk()
    {

        $user = User::factory()->create();
        $store = Store::factory()->create([
            'user_id' => $user->id,
        ]);
        $invoice = Invoice::factory()->create([
            'store_id' => $store->id,
            'currency_id' => CurrencySymbol::USD->value,
        ]);

        $currencies = Currency::where([
            ['blockchain', '!=', ''],
            ['blockchain', '!=', null],
        ])->get();
        foreach ($currencies as $currency) {
            Wallet::factory()->create([
                'store_id' => $store->id,
                'blockchain' => $currency->blockchain,
            ]);

            InvoiceAddress::factory()->create([
                'invoice_id' => $invoice->id,
                'blockchain' => $currency->blockchain,
                'currency_id' => $currency->id,
            ]);
        }

        $response = $this->get("/invoices/$invoice->id");

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJson(fn (AssertableJson $json) =>
        $json->hasAll([
                'result.id',
                'result.currency',
                'result.description',
                'result.addresses',
            ])
            ->etc()
        );


    }

    public function testGetInvoiceInfoWithAmountZeroStatusOk()
    {

        $user = User::factory()->create();
        $store = Store::factory()->create([
            'user_id' => $user->id,
        ]);
        $invoice = Invoice::factory()->create([
            'amount' => 0,
            'store_id' => $store->id,
            'currency_id' => CurrencySymbol::USD->value,
        ]);

        $currencies = Currency::where([
            ['blockchain', '!=', ''],
            ['blockchain', '!=', null],
        ])->get();
        foreach ($currencies as $currency) {
            Wallet::factory()->create([
                'store_id' => $store->id,
                'blockchain' => $currency->blockchain,
            ]);

            InvoiceAddress::factory()->create([
                'invoice_id' => $invoice->id,
                'blockchain' => $currency->blockchain,
                'currency_id' => $currency->id,
            ]);
        }

        $response = $this->get("/invoices/$invoice->id");

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJson(fn (AssertableJson $json) =>
        $json->hasAll([
            'result.id',
            'result.currency',
            'result.description',
            'result.addresses',
        ])
            ->where('result.amount', '0')
            ->etc()
        );
    }

}
