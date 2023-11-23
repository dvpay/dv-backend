<?php


namespace Tests\Api\Invoice;

use App\Enums\Blockchain;
use App\Enums\CurrencySymbol;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\InvoiceAddress;
use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use Symfony\Component\HttpFoundation\Response;
use Tests\Support\ApiTester;

class GetInvoiceInfoCest
{
    public function getInvoiceInfoStatusOk(ApiTester $I): void
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

        $I->sendGet("/invoices/$invoice->id");


        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseIsJson();
    }

    public function getInvoiceInfoWithAmountZeroStatusOk(ApiTester $I): void
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

        $I->sendGet("/invoices/$invoice->id");

        $I->seeResponseCodeIs(Response::HTTP_OK);
        $I->seeResponseIsJson();
    }
}
