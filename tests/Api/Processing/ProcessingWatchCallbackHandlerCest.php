<?php

declare(strict_types=1);

namespace Tests\Api\Processing;

use App\Enums\Blockchain;
use App\Enums\CurrencySymbol;
use App\Enums\InvoiceStatus;
use App\Enums\ProcessingCallbackType;
use App\Jobs\WatchCallbackJob;
use App\Models\Currency;
use App\Models\Invoice;
use App\Models\InvoiceAddress;
use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletBalance;
use Illuminate\Support\Facades\Queue;
use Symfony\Component\HttpFoundation\Response;
use Tests\Support\ApiTester;

class ProcessingWatchCallbackHandlerCest
{
    public function handledStatusPaidStatusOk(ApiTester $I): void
    {
        /**
         * Prepare
         */
        Queue::fake();
        $user = User::factory()->create();
        $store = Store::factory()->create([
            'user_id' => $user->id,
            'rate_source' => 'LoadRateFake',
        ]);
        $invoice = Invoice::factory()->create([
            'store_id' => $store->id,
        ]);

        $blockchains = Blockchain::cases();
        foreach ($blockchains as $blockchain) {
            $currencies = Currency::where('blockchain', $blockchain->value)->get();
            $wallet = Wallet::factory()->create([
//                    'store_id' => $store->id,
                'user_id' => $user->id,
                'blockchain' => $blockchain,
            ]);

            foreach ($currencies as $currency) {
                $walletBalance = WalletBalance::factory()->create([
                    'wallet_id' => $wallet->id,
                    'currency_id' => $currency->id,
                ]);

                $invoiceAddress = InvoiceAddress::factory()->create([
                    'invoice_id' => $invoice->id,
                    'blockchain' => $blockchain,
                    'currency_id' => $currency->id,
                ]);
            }
        }

        $txId = 'ASdaSdASdAsdASdASdAsd';
        $sender = 'zxcZXczxcZxcZxcZxczxcZXCZxc';
        $body = [
            'id' => $invoiceAddress->watch_id,
            'status' => InvoiceStatus::Paid->value,
            'tx' => $txId,
            'amount' => '0.005',
            'blockchain' => $invoiceAddress->blockchain->value,
            'address' => $invoiceAddress->address,
            'sender' => $sender,
            //            'contractAddress' => '',
            'confirmations' => '10',
            'time' => '2022-01-01 20:42:10',
            'type' => ProcessingCallbackType::Watch->value,
            'invoice_id' => $invoice->id,
        ];
        $jsonBody = json_encode($body);
        $sign = hash('sha256', $jsonBody . config('processing.client.webhookKey'));

        /**
         * Send
         */
        $I->haveHttpHeader('X-Sign', $sign);
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendPost("/processing/callback", $body);
        /**
         * Check
         */
        $I->seeResponseCodeIs(Response::HTTP_ACCEPTED);
        $I->seeResponseIsJson();
        Queue::assertPushed(WatchCallbackJob::class);
    }

    public function handledStatusExpiredStatusOk(ApiTester $I): void
    {
        /**
         * Prepare
         */
        Queue::fake();
        $user = User::factory()->create();
        $store = Store::factory()->create([
            'user_id' => $user->id,
            'rate_source' => 'LoadRateFake',
        ]);
        $invoice = Invoice::factory()->create([
            'store_id' => $store->id,
        ]);

        $blockchains = Blockchain::cases();
        foreach ($blockchains as $blockchain) {
            $currencies = Currency::where('blockchain', $blockchain->value)->get();
            $wallet = Wallet::factory()->create([
                'store_id' => $store->id,
                'blockchain' => $blockchain,
            ]);

            foreach ($currencies as $currency) {
                $walletBalance = WalletBalance::factory()->create([
                    'wallet_id' => $wallet->id,
                    'currency_id' => $currency->id,
                ]);

                $invoiceAddress = InvoiceAddress::factory()->create([
                    'invoice_id' => $invoice->id,
                    'blockchain' => $blockchain,
                    'currency_id' => $currency->id,
                ]);
            }
        }

        $body = [
            'id' => $invoiceAddress->watch_id,
            'status' => InvoiceStatus::Expired->value,
            'blockchain' => $invoiceAddress->blockchain->value,
            'address' => $invoiceAddress->address,
//            'contractAddress' => '',
            'type' => ProcessingCallbackType::Watch->value,
            'tx' => 'asdasdasdasd',
            'invoice_id' => $invoice->id,
        ];
        $jsonBody = json_encode($body);
        $sign = hash('sha256', $jsonBody . config('processing.client.webhookKey'));

        /**
         * Send
         */
        $I->haveHttpHeader('X-Sign', $sign);
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendPost("/processing/callback", $body);

        /**
         * Check
         */
        $I->seeResponseCodeIs(Response::HTTP_ACCEPTED);
        $I->seeResponseIsJson();
        Queue::assertPushed(WatchCallbackJob::class);

    }

    public function handledStatusPaidInvoiceAmountZeroStatusOk(ApiTester $I): void
    {
        /**
         * Prepare
         */
        Queue::fake();

        $user = User::factory()->create();
        $store = Store::factory()->create([
            'user_id' => $user->id,
            'rate_source' => 'LoadRateFake',
        ]);
        $invoice = Invoice::factory()->create([
            'amount' => 0,
            'store_id' => $store->id,
        ]);

        $blockchains = Blockchain::cases();
        foreach ($blockchains as $blockchain) {
            $currencies = Currency::where('blockchain', $blockchain->value)->get();
            $wallet = Wallet::factory()->create([
                'store_id' => $store->id,
                'blockchain' => $blockchain,
            ]);

            foreach ($currencies as $currency) {
                $walletBalance = WalletBalance::factory()->create([
                    'wallet_id' => $wallet->id,
                    'currency_id' => $currency->id,
                ]);

                $invoiceAddress = InvoiceAddress::factory()->create([
                    'invoice_id' => $invoice->id,
                    'blockchain' => $blockchain,
                    'currency_id' => $currency->id,
                ]);
            }
        }

        $txId = 'ASdaSdASdAsdASdASdAsd';
        $sender = 'zxcZXczxcZxcZxcZxczxcZXCZxc';
        $body = [
            'id' => $invoiceAddress->watch_id,
            'status' => InvoiceStatus::Paid->value,
            'tx' => $txId,
            'amount' => '0.005',
            'blockchain' => $invoiceAddress->blockchain->value,
            'address' => $invoiceAddress->address,
            'sender' => $sender,
//            'contractAddress' => '',
            'confirmations' => '10',
            'time' => '2022-01-01 20:42:10',
            'type' => ProcessingCallbackType::Watch->value,
            'invoice_id' => $invoice->id,
        ];
        $jsonBody = json_encode($body);
        $sign = hash('sha256', $jsonBody . config('processing.client.webhookKey'));

        /**
         * Send
         */
        $I->haveHttpHeader('X-Sign', $sign);
        $I->haveHttpHeader('Accept', 'application/json');

        $I->sendPost("/processing/callback", $body);

        /**
         * Check
         */
        $I->seeResponseCodeIs(Response::HTTP_ACCEPTED);
        $I->seeResponseIsJson();
        Queue::assertPushed(WatchCallbackJob::class);
    }

    public function handledStatusWaitingConfirmationsInvoiceStatusOk(ApiTester $I): void
    {
        /**
         * Prepare
         */
        Queue::fake();
        $user = User::factory()->create();
        $store = Store::factory()->create([
            'user_id' => $user->id,
            'rate_source' => 'LoadRateFake',
        ]);
        $invoice = Invoice::factory()->create([
            'amount' => 0,
            'store_id' => $store->id,
        ]);

        $blockchains = Blockchain::cases();
        foreach ($blockchains as $blockchain) {
            $currencies = Currency::where('blockchain', $blockchain->value)->get();
            $wallet = Wallet::factory()->create([
                'store_id' => $store->id,
                'blockchain' => $blockchain,
            ]);

            foreach ($currencies as $currency) {
                $walletBalance = WalletBalance::factory()->create([
                    'wallet_id' => $wallet->id,
                    'currency_id' => $currency->id,
                ]);

                $invoiceAddress = InvoiceAddress::factory()->create([
                    'invoice_id' => $invoice->id,
                    'blockchain' => $blockchain,
                    'currency_id' => $currency->id,
                ]);
            }
        }

        $txId = 'ASdaSdASdAsdASdASdAsd';
        $sender = 'zxcZXczxcZxcZxcZxczxcZXCZxc';
        $body = [
            'id' => $invoiceAddress->watch_id,
            'status' => InvoiceStatus::Paid->value,
            'tx' => $txId,
            'amount' => '0.005',
            'blockchain' => $invoiceAddress->blockchain->value,
            'address' => $invoiceAddress->address,
            'sender' => $sender,
//            'contractAddress' => '',
            'confirmations' => '0',
            'time' => '2022-01-01 20:42:10',
            'type' => ProcessingCallbackType::Watch->value,
            'invoice_id' => $invoice->id,
        ];
        $jsonBody = json_encode($body);
        $sign = hash('sha256', $jsonBody . config('processing.client.webhookKey'));

        /**
         * Send
         */
        $I->haveHttpHeader('X-Sign', $sign);
        $I->haveHttpHeader('Accept', 'application/json');

        $I->sendPost("/processing/callback", $body);

        /**
         * Check
         */
        $I->seeResponseCodeIs(Response::HTTP_ACCEPTED);
        $I->seeResponseIsJson();
        Queue::assertPushed(WatchCallbackJob::class);

    }

    public function invalidSignStatusUnauthorized(ApiTester $I): void
    {
        /**
         * Prepare
         */
        Queue::fake();
        $user = User::factory()->create();
        $store = Store::factory()->create([
            'user_id' => $user->id,
            'rate_source' => 'LoadRateFake',
        ]);
        $invoice = Invoice::factory()->create([
            'store_id' => $store->id,
        ]);

        $blockchains = Blockchain::cases();
        foreach ($blockchains as $blockchain) {
            $currencies = Currency::where('blockchain', $blockchain->value)->get();
            $wallet = Wallet::factory()->create([
                'store_id' => $store->id,
                'blockchain' => $blockchain,
            ]);

            foreach ($currencies as $currency) {
                $walletBalance = WalletBalance::factory()->create([
                    'wallet_id' => $wallet->id,
                    'currency_id' => $currency->id,
                ]);

                $invoiceAddress = InvoiceAddress::factory()->create([
                    'invoice_id' => $invoice->id,
                    'blockchain' => $blockchain,
                    'currency_id' => $currency->id,
                ]);
            }
        }

        $txId = 'ASdaSdASdAsdASdASdAsd';
        $sender = 'zxcZXczxcZxcZxcZxczxcZXCZxc';
        $body = [
            'id' => $invoiceAddress->watch_id,
            'status' => InvoiceStatus::Paid->value,
            'tx' => $txId,
            'amount' => (string)$invoice->amount,
            'blockchain' => $invoiceAddress->blockchain->value,
            'address' => $invoiceAddress->address,
            'sender' => $sender,
//            'contractAddress' => '',
            'confirmations' => '10',
            'time' => '2022-01-01 20:42:10',
            'type' => ProcessingCallbackType::Watch->value,
            'invoice_id' => $invoice->id,
        ];
        $jsonBody = json_encode($body);
        $sign = hash('sha256', $jsonBody . config('processing.client.webhookKey'));

        /**
         * Send
         */
        $I->haveHttpHeader('X-Sign', 'ABC');
        $I->haveHttpHeader('Accept', 'application/json');

        $I->sendPost("/processing/callback", $body);

        /**
         * Check
         */
        $I->seeResponseCodeIs(Response::HTTP_I_AM_A_TEAPOT);
        $I->seeResponseIsJson();
        Queue::assertNotPushed(WatchCallbackJob::class);
    }

    public function handledStatusPartiallyPaidStatusOk(ApiTester $I): void
    {
        /**
         * Prepare
         */
        Queue::fake();
        $user = User::factory()->create();
        $store = Store::factory()->create([
            'user_id' => $user->id,
            'rate_source' => 'LoadRateFake',
        ]);
        $invoice = Invoice::factory()->create([
            'store_id' => $store->id,
        ]);

        $blockchains = Blockchain::cases();
        foreach ($blockchains as $blockchain) {
            $currencies = Currency::where('blockchain', $blockchain->value)->get();
            $wallet = Wallet::factory()->create([
                'store_id' => $store->id,
                'blockchain' => $blockchain,
            ]);

            foreach ($currencies as $currency) {
                $walletBalance = WalletBalance::factory()->create([
                    'wallet_id' => $wallet->id,
                    'currency_id' => $currency->id,
                ]);

                $invoiceAddress = InvoiceAddress::factory()->create([
                    'invoice_id' => $invoice->id,
                    'blockchain' => $blockchain,
                    'currency_id' => $currency->id,
                ]);
            }
        }

        $invoiceAddress = InvoiceAddress::where([
            ['invoice_id', $invoice->id],
            ['blockchain', Blockchain::Tron],
        ])->first();

        $txId = 'ASdaSdASdAsdASdASdAsd';
        $sender = 'zxcZXczxcZxcZxcZxczxcZXCZxc';
        $body = [
            'id' => $invoiceAddress->watch_id,
            'status' => InvoiceStatus::PartiallyPaid->value,
            'tx' => $txId,
            'amount' => (string)(50 * $invoiceAddress->rate),
            'blockchain' => $invoiceAddress->blockchain->value,
            'address' => $invoiceAddress->address,
            'sender' => $sender,
//            'contractAddress' => '',
            'confirmations' => '10',
            'time' => '2022-01-01 20:42:10',
            'type' => ProcessingCallbackType::Watch->value,
            'invoice_id' => $invoice->id,
        ];
        $jsonBody = json_encode($body);
        $sign = hash('sha256', $jsonBody . config('processing.client.webhookKey'));

        /**
         * Send
         */
        $I->haveHttpHeader('X-Sign', $sign);
        $I->haveHttpHeader('Accept', 'application/json');

        $I->sendPost("/processing/callback", $body);

        /**
         * Check
         */
        $I->seeResponseCodeIs(Response::HTTP_ACCEPTED);
        $I->seeResponseIsJson();
        Queue::assertPushed(WatchCallbackJob::class);

    }

    public function handledStatusLastPartiallyPaidStatusOk(ApiTester $I): void
    {
        /**
         * Prepare
         */
        Queue::fake();
        $user = User::factory()->create();
        $store = Store::factory()->create([
            'user_id' => $user->id,
            'rate_source' => 'LoadRateFake',
        ]);
        $invoice = Invoice::factory()->create([
            'store_id' => $store->id,
            'amount' => 100,
        ]);

        $blockchains = Blockchain::cases();
        foreach ($blockchains as $blockchain) {
            $currencies = Currency::where('blockchain', $blockchain->value)->get();
            $wallet = Wallet::factory()->create([
                'store_id' => $store->id,
                'blockchain' => $blockchain,
            ]);

            foreach ($currencies as $currency) {
                $walletBalance = WalletBalance::factory()->create([
                    'wallet_id' => $wallet->id,
                    'currency_id' => $currency->id,
                ]);

                $invoiceAddress = InvoiceAddress::factory()->create([
                    'invoice_id' => $invoice->id,
                    'blockchain' => $blockchain,
                    'currency_id' => $currency->id,
                ]);
            }
        }

        $invoiceAddress = InvoiceAddress::where([
            ['invoice_id', $invoice->id],
            ['blockchain', Blockchain::Tron],
        ])->first();

        /**
         * Send
         */
        $sender = 'zxcZXczxcZxcZxcZxczxcZXCZxc';
        $body = [
            'id' => $invoiceAddress->watch_id,
            'status' => InvoiceStatus::PartiallyPaid->value,
            'tx' => 'ASdaSdASdAsdASdASdAsd',
            'amount' => (string)(50 * $invoiceAddress->rate),
            'blockchain' => $invoiceAddress->blockchain->value,
            'address' => $invoiceAddress->address,
            'sender' => $sender,
//            'contractAddress' => '',
            'confirmations' => '10',
            'time' => '2022-01-01 20:42:10',
            'type' => ProcessingCallbackType::Watch->value,
            'invoice_id' => $invoice->id,
        ];
        $jsonBody = json_encode($body);
        $sign = hash('sha256', $jsonBody . config('processing.client.webhookKey'));

        $I->haveHttpHeader('X-Sign', $sign);
        $I->haveHttpHeader('Accept', 'application/json');

        $I->sendPost("/processing/callback", $body);

        $body['tx'] = 'afdsfDFsdfsdFDFDsdfsdffSDffsd';
        $jsonBody = json_encode($body);
        $sign = hash('sha256', $jsonBody . config('processing.client.webhookKey'));
        $I->haveHttpHeader('X-Sign', $sign);
        $I->haveHttpHeader('Accept', 'application/json');

        $I->sendPost("/processing/callback", $body);

        /**
         * Check
         */
        $I->seeResponseCodeIs(Response::HTTP_ACCEPTED);

    }
}