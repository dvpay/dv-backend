<?php


namespace Tests\Api\Auth;

use App\Models\User;
use Tests\Support\ApiTester;

class LoginCest
{
    public function _before(ApiTester $I)
    {
    }

    public function statusOk(ApiTester $I)
    {
        $user = User::factory()->create();

        $I->sendPost('/auth/login', [
            'email' => $user->email,
            'password' => '123456',
        ]);

        $I->seeResponseCodeIsSuccessful();
        $I->seeResponseIsJson();
    }
}
