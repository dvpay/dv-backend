<?php

declare(strict_types=1);

namespace Tests\Api\Processing;

use App\Enums\Blockchain;
use App\Enums\ProcessingCallbackType;
use App\Models\Currency;
use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletBalance;
use Symfony\Component\HttpFoundation\Response;
use Tests\Support\ApiTester;

class ProcessingTransferCallbackHandlerCest
{
    public function handledStatusOk(ApiTester $I): void
    {
        /**
         * Prepare
         */
        $user = User::factory()->create();
        Store::factory()->create([
            'user_id' => $user->id,
            'processing_owner_id' => 'asdasdasdASD',
        ]);

        $blockchains = Blockchain::cases();
        foreach ($blockchains as $blockchain) {
            $currencies = Currency::where('blockchain', $blockchain->value)->get();
            $wallet = Wallet::factory()->create([
                'user_id' => $user->id,
                'blockchain' => $blockchain,
            ]);

            foreach ($currencies as $currency) {
                WalletBalance::factory()->create([
                    'wallet_id' => $wallet->id,
                    'currency_id' => $currency->id,
                    'balance' => 100,
                ]);
            }
        }

        $txId = 'ASdaSdASdAsdASdASdAsd';
        $sender = 'zxcZXczxcZxcZxcZxczxcZXCZxc';
        $body = [
            'id' => $user->processing_owner_id,
            'tx' => $txId,
            'amount' => '100',
            'blockchain' => $wallet->blockchain->value,
            'address' => $wallet->address,
            'sender' => $sender,
            'contractAddress' => $currency->contract_address,
            'time' => '2022-01-01 20:42:10',
            'type' => ProcessingCallbackType::Transfer->value,
            'isManual' => "1",
            'ownerId' => $user->processing_owner_id,
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
        $I->seeNumRecords(1, 'transactions', [
            'user_id' => $user->id,
//            'store_id' => $store->id,
            'tx_id' => $txId,
            'from_address' => $sender,
            'to_address' => $wallet->address,
        ]);
//        $I->seeNumRecords(1, 'wallet_balances', [
//            'wallet_id' => $wallet->id,
//            'currency_id' => $currency->id,
//            'balance' => 0,
//        ]);
    }

    public function handledNegativeAmountStatusBadRequest(ApiTester $I): void
    {
        /**
         * Prepare
         */
        $user = User::factory()->create();
        Store::factory()->create([
            'user_id' => $user->id,
            'processing_owner_id' => 'asdasdasdASD',
        ]);

        $blockchains = Blockchain::cases();
        foreach ($blockchains as $blockchain) {
            $currencies = Currency::where('blockchain', $blockchain->value)->get();
            $wallet = Wallet::factory()->create([
                'user_id' => $user->id,
                'blockchain' => $blockchain,
            ]);

            foreach ($currencies as $currency) {
                WalletBalance::factory()->create([
                    'wallet_id' => $wallet->id,
                    'currency_id' => $currency->id,
                    'balance' => 100,
                ]);
            }
        }

        $txId = 'ASdaSdASdAsdASdASdAsd';
        $sender = 'zxcZXczxcZxcZxcZxczxcZXCZxc';
        $body = [
            'id' => $user->processing_owner_id,
            'tx' => $txId,
            'amount' => '-100',
            'blockchain' => $wallet->blockchain->value,
            'address' => $wallet->address,
            'sender' => $sender,
            'contractAddress' => $currency->contract_address,
            'time' => '2022-01-01 20:42:10',
            'type' => ProcessingCallbackType::Transfer->value,
            'isManual' => "1",
            'ownerId' => $user->processing_owner_id,
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
        $I->seeResponseCodeIs(Response::HTTP_BAD_REQUEST);
        $I->seeResponseIsJson();
        $I->seeNumRecords(0, 'transactions', [
            'user_id' => $user->id,
//            'store_id' => $store->id,
            'tx_id' => $txId,
            'from_address' => $sender,
            'to_address' => $wallet->address,
        ]);
//        $I->seeNumRecords(0, 'wallet_balances', [
//            'wallet_id' => $wallet->id,
//            'currency_id' => $currency->id,
//            'balance' => 0,
//        ]);
    }
}