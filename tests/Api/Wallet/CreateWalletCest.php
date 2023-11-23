<?php


namespace Tests\Api\Wallet;

use App\Enums\Blockchain;
use App\Models\Currency;
use App\Models\InvoiceAddress;
use App\Models\Store;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletBalance;
use Symfony\Component\HttpFoundation\Response;
use Tests\Support\ApiTester;

class CreateWalletCest
{
//    public function createWalletStatusOk(ApiTester $I): void
//    {
//        $user = User::factory()->create();
//        $store = Store::factory()->create([
//            'user_id' => $user->id,
//        ]);
//
//        $I->login($user->email, '123456');
//
//        $res = $I->sendPost("/stores/wallets/create");
//
//        $I->seeResponseCodeIs(Response::HTTP_OK);
//        $I->seeResponseIsJson();
//
//        $blockchains = Blockchain::cases();
//        foreach ($blockchains as $blockchain) {
//            $currencyExists = Currency::where([
//                ['blockchain', $blockchain],
//                ['has_balance', true],
//            ])->exists();
//
//            if (!$currencyExists) {
//                continue;
//            }
//
//            $wallet = Wallet::where([
//                ['user_id', $user->id],
//                ['blockchain', $blockchain],
//            ])->first();
//
//            $I->seeNumRecords(1, 'wallets', [
//                'user_id' => $user->id,
//                'blockchain' => $blockchain,
//            ]);
//        }
//    }
//
//    public function createWalletStatusUnauthorized(ApiTester $I): void
//    {
//        $user = User::factory()->create();
//        $store = Store::factory()->create([
//            'user_id' => $user->id,
//        ]);
//
//        $I->haveHttpHeader('Authorization', 'Bearer AAABBBCCC');
//        $I->haveHttpHeader('Accept', 'application/json');
//
//        $response = $I->sendPost("/stores/wallets/create", [
//            "isHot" => true,
//            "mnemonic" => "asdasdawDASDAW ASDawdasdas asdasdaw",
//            "passPhrase" => "Pass",
//            "passPhrase_confirmation" => "Pass",
//            "remember" => true,
//        ]);
//
//        $I->seeResponseCodeIs(Response::HTTP_UNAUTHORIZED);
//        $I->seeResponseIsJson();
//    }
//
//    public function createWalletWhenWalletIsSetStatusOk(ApiTester $I): void
//    {
//        $user = User::factory()->create();
//        $store = Store::factory()->create([
//            'user_id' => $user->id,
//        ]);
//
//        $blockchains = Blockchain::cases();
//        foreach ($blockchains as $blockchain) {
//            $wallet = Wallet::factory()->create([
//                'store_id' => $store->id,
//                'blockchain' => $blockchain,
//            ]);
//
//            $currencies = Currency::where('blockchain', $blockchain->value)->get();
//            foreach ($currencies as $currency) {
//                WalletBalance::factory()->create([
//                    'wallet_id' => $wallet->id,
//                    'currency_id' => $currency->id,
//                ]);
//            }
//        }
//
////        $mnemonic = 'asdasdawDASDAW ASDawdasdas asdasdaw';
////        $wallet = Wallet::factory()->create([
////            'store_id' => $store->id,
////            'seed' => $mnemonic,
////            'pass_phrase' => 'Asd',
////        ]);
//
//        $I->login($user->email, '123456');
//        $I->sendPost("/stores/wallets/create", [
//            "remember" => true,
//            "readonly" => 0,
//            "mnemonic" => "asdasfaw ads da sds d sasdaw",
//            "passPhrase" => "Pass",
//            "passPhrase_confirmation" => "Pass",
//        ]);
//
//        $I->seeResponseCodeIs(Response::HTTP_OK);
//        $I->seeResponseIsJson();
//        $I->seeNumRecords(1, 'wallets', [
//            'id' => $wallet->id,
//            'deleted_at' => null,
//        ]);
//    }
}
