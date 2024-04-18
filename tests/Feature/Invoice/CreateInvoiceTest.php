<?php

namespace Tests\Feature\Invoice;

 use App\Enums\Blockchain;
 use App\Enums\CurrencySymbol;
 use App\Enums\UserRole;
 use App\Models\Currency;
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

class CreateInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function testAdminCanCreateInvoice()
    {

        $user = User::factory()->create();
        $user->assignRole([UserRole::Admin->value]);

        $store = Store::factory()->create([
            'user_id' => $user->id,
            'rate_source' => 'LoadRateFake',
        ]);

        $blockchains = Blockchain::cases();
        foreach ($blockchains as $blockchain) {
            $wallet = Wallet::factory()->create([
                'store_id' => $store->id,
                'blockchain' => $blockchain,
            ]);

            $currencies = Currency::where('blockchain', $blockchain->value)->get();
            foreach ($currencies as $currency) {
                WalletBalance::factory()->create([
                    'wallet_id' => $wallet->id,
                    'currency_id' => $currency->id
                ]);
            }
        }

        $storeApiKey = StoreApiKey::factory()->create([
            'store_id' => $store->id,
        ]);

        $response = $this->post('/invoices', [
            'orderId' => 'Order-ID',
            'amount' => 100.5,
            'currency' => CurrencySymbol::USD->value,
            'description' => 'Test',
            'returnUrl' => 'http://test.url',
            'successUrl' => 'http://test.url',
        ],[
            'X-Api-Key' => $storeApiKey->key,
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson(fn (AssertableJson $json) =>
        $json->hasAll([
            'result.paymentUrl',
            'result.invoiceId',
            ])
            ->etc()
        );
        $this->assertDatabaseHas('invoices',[
            'store_id' => $store->id,
            'order_id' => 'Order-ID',
        ]);
    }

    public function testAdminCanCreateInvoiceWithZeroAmount()
    {

        $user = User::factory()->create();
        $user->assignRole([UserRole::Admin->value]);

        $store = Store::factory()->create([
            'user_id' => $user->id,
            'rate_source' => 'LoadRateFake',
        ]);

        $blockchains = Blockchain::cases();
        foreach ($blockchains as $blockchain) {
            $wallet = Wallet::factory()->create([
                'store_id' => $store->id,
                'blockchain' => $blockchain,
            ]);

            $currencies = Currency::where('blockchain', $blockchain->value)->get();
            foreach ($currencies as $currency) {
                WalletBalance::factory()->create([
                    'wallet_id' => $wallet->id,
                    'currency_id' => $currency->id
                ]);
            }
        }

        $storeApiKey = StoreApiKey::factory()->create([
            'store_id' => $store->id,
        ]);

        $response = $this->post('/invoices', [
            'orderId' => 'Order-ID',
            'currency' => CurrencySymbol::USD->value,
            'description' => 'Test',
            'returnUrl' => 'http://test.url',
            'successUrl' => 'http://test.url',
        ],[
            'X-Api-Key' => $storeApiKey->key,
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson(fn (AssertableJson $json) =>
        $json->hasAll([
            'result.paymentUrl',
            'result.invoiceId',
        ])
            ->etc()
        );
        $this->assertDatabaseHas('invoices',[
            'amount' => 0,
            'store_id' => $store->id,
            'order_id' => 'Order-ID',
        ]);
    }

    public function testCreateInvoiceStatusUnauthorized()
    {
        $user = User::factory()->create();
        $user->assignRole([UserRole::Admin->value]);

        $store = Store::factory()->create([
            'user_id' => $user->id,
            'rate_source' => 'LoadRateFake',
        ]);

        $blockchains = Blockchain::cases();
        foreach ($blockchains as $blockchain) {
            $wallet = Wallet::factory()->create([
                'store_id' => $store->id,
                'blockchain' => $blockchain,
            ]);

            $currencies = Currency::where('blockchain', $blockchain->value)->get();
            foreach ($currencies as $currency) {
                WalletBalance::factory()->create([
                    'wallet_id' => $wallet->id,
                    'currency_id' => $currency->id
                ]);
            }
        }

        $response = $this->post('/invoices', [
            'orderId' => 'Order-ID',
            'currency' => CurrencySymbol::USD->value,
            'description' => 'Test',
            'returnUrl' => 'http://test.url',
            'successUrl' => 'http://test.url',
        ],[
            'X-Api-Key' => 'avada kedavra',
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson(fn (AssertableJson $json) =>
        $json->has('errors')
            ->etc()
        );
    }

    public function testCreateInvoiceWhenStoreWithoutWalletsStatusCreated(): void
    {

        $user = User::factory()->create();
        $user->assignRole([UserRole::Admin->value]);

        $store = Store::factory()->create([
            'user_id' => $user->id,
            'rate_source' => 'LoadRateFake',
        ]);


        $storeApiKey = StoreApiKey::factory()->create([
            'store_id' => $store->id,
        ]);

        $response = $this->post('/invoices', [
            'orderId' => 'Order-ID',
            'amount' => 100.5,
            'currency' => CurrencySymbol::USD->value,
            'description' => 'Test',
            'returnUrl' => 'http://test.url',
            'successUrl' => 'http://test.url',
        ],[
            'X-Api-Key' => $storeApiKey->key,
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson(fn (AssertableJson $json) =>
        $json->hasAll([
            'result.paymentUrl',
            'result.invoiceId',
        ])
            ->etc()
        );
        $this->assertDatabaseHas('invoices',[
            'store_id' => $store->id,
            'order_id' => 'Order-ID',
        ]);

    }

    public function testCreateInvoiceUseNotFiatCurrencyStatusBadRequest(): void
    {

        $user = User::factory()->create();
        $user->assignRole([UserRole::Admin->value]);

        $store = Store::factory()->create([
            'user_id' => $user->id,
            'rate_source' => 'LoadRateFake',
        ]);


        $storeApiKey = StoreApiKey::factory()->create([
            'store_id' => $store->id,
        ]);

        $response = $this->post('/invoices', [
            'orderId' => 'Order-ID',
            'amount' => 100.5,
            'currency' => CurrencySymbol::BTC->value,
            'description' => 'Test',
            'returnUrl' => 'http://test.url',
            'successUrl' => 'http://test.url',
        ],[
            'X-Api-Key' => $storeApiKey->key,
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson(fn (AssertableJson $json) =>
        $json->hasAll([
            'message',
            'errors.currency',
        ])
            ->etc()
        );

    }

}
