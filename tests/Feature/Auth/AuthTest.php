<?php

namespace Tests\Feature\Auth;

 use App\Models\User;
 use Illuminate\Foundation\Testing\RefreshDatabase;
 use Illuminate\Testing\Fluent\AssertableJson;
 use Symfony\Component\HttpFoundation\Response;
 use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanLogin()
    {
        $user = User::factory()->create();

        $response = $this->post('/auth/login', [
            'email' => $user->email,
            'password' => '123456',
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('result.token')
                ->etc()
        );
    }
}
