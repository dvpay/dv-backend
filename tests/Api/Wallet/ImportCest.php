<?php


namespace Tests\Api\Wallet;

use App\Enums\Blockchain;
use App\Models\Store;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Tests\Support\ApiTester;

class ImportCest
{
//    public function mnemonicStatusCreated(ApiTester $I): void
//    {
//        $user = User::factory()->create();
//        $store = Store::factory()->create([
//            'user_id' => $user->id,
//        ]);
//
//        $I->login($user->email, '123456');
//
//        $mnemonic = "asdasdawDASDAW ASDawdasdas asdasdaw";
//        $passPhrase = "Pass";
//
//        $blockchains = Blockchain::cases();
//        foreach ($blockchains as $blockchain) {
//            $response = $I->sendPut("/stores/$store->id/wallets/$blockchain->value/mnemonic", [
//                "mnemonic" => $mnemonic,
//                "passPhrase" => $passPhrase,
//                "passPhrase_confirmation" => $passPhrase,
//            ]);
//
//            $I->seeResponseCodeIs(Response::HTTP_CREATED);
//            $I->seeResponseIsJson();
//            $I->seeNumRecords(1, 'wallets', [
//                'store_id' => $store->id,
//                'blockchain' => $blockchain->value,
//                'seed' => $mnemonic,
//                'pass_phrase' => $passPhrase,
//            ]);
//        }
//    }
//
//    public function addressStatusCreated(ApiTester $I): void
//    {
//        $user = User::factory()->create();
//        $store = Store::factory()->create([
//            'user_id' => $user->id,
//        ]);
//
//        $I->login($user->email, '123456');
//
//        $address = "asdasdawDASDAW ASDawdasdas asdasdaw";
//
//        $blockchains = Blockchain::cases();
//        foreach ($blockchains as $blockchain) {
//            $I->sendPut("/stores/$store->id/wallets/$blockchain->value/address", [
//                "address" => $address,
//            ]);
//
//            $I->seeResponseCodeIs(Response::HTTP_CREATED);
//            $I->seeResponseIsJson();
//            $I->seeNumRecords(1, 'wallets', [
//                'store_id' => $store->id,
//                'blockchain' => $blockchain->value,
//                'address' => $address,
//            ]);
//        }
//    }
//
//    public function unexpectedAuthKeyStatusUnauthorized(ApiTester $I): void
//    {
//        $user = User::factory()->create();
//        $store = Store::factory()->create([
//            'user_id' => $user->id,
//        ]);
//
//        $I->haveHttpHeader('Authorization', 'Bearer AAABBBCCC');
//        $I->sendPut("/stores/$store->id/wallets/tron/address", [
//            "address" => "asdasdawDASDAW-ASDawdasdas-asdasdaw",
//        ]);
//
//        $I->seeResponseCodeIs(Response::HTTP_UNAUTHORIZED);
//        $I->seeResponseIsJson();
//    }
//
//    public function editSomeoneStoreStatusUnauthorized(ApiTester $I): void
//    {
//        $user = User::factory()->create();
//        $store = Store::factory()->create([
//            'user_id' => $user->id,
//        ]);
//
//        $I->login($user->email, '123456');
//        $I->sendPut("/stores/ASDASDASD/wallets/tron/address", [
//            "address" => "asdasdawDASDAW-ASDawdasdas-asdasdaw",
//        ]);
//
//        $I->seeResponseCodeIs(Response::HTTP_NOT_FOUND);
//        $I->seeResponseIsJson();
//    }
}
