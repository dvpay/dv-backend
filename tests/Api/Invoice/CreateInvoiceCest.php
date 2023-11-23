<?php


namespace Tests\Api\Invoice;

use App\Enums\Blockchain;
use App\Enums\CurrencySymbol;
use App\Models\Currency;
use App\Models\RateSource;
use App\Models\Store;
use App\Models\StoreApiKey;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletBalance;
use Symfony\Component\HttpFoundation\Response;
use Tests\Support\ApiTester;

class CreateInvoiceCest
{
    public function createInvoiceStatusCreated(ApiTester $I): void
    {
        $user = User::factory()->create();
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

        $I->haveHttpHeader('X-Api-Key', $storeApiKey->key);
        $I->haveHttpHeader('Accept', 'application/json');

        $I->sendPost("invoices", [
            'orderId' => 'Order-ID',
            'amount' => 100.5,
            'currency' => CurrencySymbol::USD->value,
            'description' => 'Test',
            'returnUrl' => 'http://test.url',
            'successUrl' => 'http://test.url',
        ]);

        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseIsJson();
        $I->seeNumRecords(1, 'invoices', [
            'store_id' => $store->id,
            'order_id' => 'Order-ID',
        ]);
    }

    public function createInvoiceWithAmountZeroStatusCreated(ApiTester $I): void
    {
        $user = User::factory()->create();
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

        $I->haveHttpHeader('X-Api-Key', $storeApiKey->key);
        $I->haveHttpHeader('Accept', 'application/json');

        $I->sendPost("invoices", [
            'orderId' => 'Order-ID',
            'currencyCode' => CurrencySymbol::USD->value,
            'description' => 'Test',
            'returnUrl' => 'http://test.url',
            'successUrl' => 'http://test.url',
        ]);

        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseIsJson();
        $I->seeNumRecords(1, 'invoices', [
            'amount' => 0,
            'store_id' => $store->id,
            'order_id' => 'Order-ID',
        ]);
    }

    public function createInvoiceStatusUnauthorized(ApiTester $I): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->create([
            'user_id' => $user->id,
        ]);
        $storeApiKey = StoreApiKey::factory()->create([
            'store_id' => $store->id,
        ]);

        $I->haveHttpHeader('X-Api-Key', 'blablabla');
        $I->haveHttpHeader('Accept', 'application/json');

        $I->sendPost("invoices", [
            'orderId' => 'Order-ID',
            'amount' => 100.5,
            'currency' => CurrencySymbol::USD->value,
            'description' => 'Test',
            'returnUrl' => 'http://test.url',
            'successUrl' => 'http://test.url',
        ]);

        $I->seeResponseCodeIs(Response::HTTP_UNAUTHORIZED);
        $I->seeResponseIsJson();
    }

    public function createInvoiceWhenStoreWithoutWalletsStatusCreated(ApiTester $I): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->create([
            'user_id' => $user->id,
        ]);
        $storeApiKey = StoreApiKey::factory()->create([
            'store_id' => $store->id,
        ]);

        $I->haveHttpHeader('X-Api-Key', $storeApiKey->key);
        $I->haveHttpHeader('Accept', 'application/json');

        $I->sendPost("invoices", [
            'orderId' => 'Order-ID',
            'amount' => 100.5,
            'currency' => CurrencySymbol::USD->value,
            'description' => 'Test',
            'returnUrl' => 'http://test.url',
            'successUrl' => 'http://test.url',
        ]);

        $I->seeResponseCodeIs(Response::HTTP_CREATED);
        $I->seeResponseIsJson();
        $I->seeNumRecords(1, 'invoices');
    }

    public function createInvoiceUseNotFiatCurrencyStatusBadRequest(ApiTester $I): void
    {
        $user = User::factory()->create();
        $store = Store::factory()->create([
            'user_id' => $user->id,
        ]);
        $storeApiKey = StoreApiKey::factory()->create([
            'store_id' => $store->id,
        ]);

        $I->haveHttpHeader('X-Api-Key', $storeApiKey->key);
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendPost("invoices", [
            'orderId' => 'Order-ID',
            'amount' => 100.5,
            'currency' => CurrencySymbol::BTC->value,
            'description' => 'Test',
            'returnUrl' => 'http://test.url',
            'successUrl' => 'http://test.url',
        ]);

        $I->seeResponseCodeIs(Response::HTTP_UNPROCESSABLE_ENTITY);
        $I->seeResponseIsJson();
        $I->seeNumRecords(0, 'invoices');
        $I->seeNumRecords(0, 'invoice_addresses');
    }
}
