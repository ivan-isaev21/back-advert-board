<?php

namespace Tests\Feature\Api\v1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    private $authUrl = '/api/v1/auth/login';

    /**
     * Method testEmptyPayload
     *
     * @return void
     */
    public function testEmptyPayload(): void
    {
        $response = $this->postJson($this->authUrl);

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The email field is required. (and 1 more error)',
            'errors' => [
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.'],
            ]
        ]);
    }

    /**
     * Method testYouNeedConfirmEmail
     *
     * @return void
     */
    public function testYouNeedConfirmEmail(): void
    {
        User::register(
            $name = 'test',
            $email = 'test-email@test.com',
            $password = 'Aa123456789'
        );

        $payload = [
            'email' => $email,
            'password' => $password
        ];

        $response = $this->postJson($this->authUrl, $payload);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'You need to confirm your account. Please check your email.'
        ]);
    }

    /**
     * Method testPhoneAuthEnabled
     *
     * @return void
     */
    public function testPhoneAuthEnabled(): void
    {
        $email = 'test-email@test.com';
        $password = 'Aa123456789';

        User::factory()->create([
            'email' => $email,
            'password' => $password,
            'phone' => '+380000000000',
            'phone_verified' => true,
            'phone_verify_token' => null,
            'phone_verify_token_expire' => null,
            'phone_auth' => true,
            'status' => User::STATUS_ACTIVE
        ]);

        $payload = [
            'email' => $email,
            'password' => $password
        ];

        $response = $this->postJson($this->authUrl, $payload);

        $response->assertStatus(202);
        $response->assertJson([
            'message' => 'Please enter the login code sent to your phone.'
        ]);
    }

    /**
     * Method testSuccess
     *
     * @return void
     */
    public function testSuccess(): void
    {
        $email = 'test-email@test.com';
        $password = 'Aa123456789';

        $user = User::factory()->create([
            'email' => $email,
            'password' => $password,
            'phone_auth' => false,
            'status' => User::STATUS_ACTIVE
        ]);

        $payload = [
            'email' => $email,
            'password' => $password
        ];

        $response = $this->postJson($this->authUrl, $payload);

        $response->assertStatus(202);
        $response->assertJson([
            'token_type' => 'Bearer'
        ]);
    }

    /**
     * Method testWrong
     *
     * @return void
     */
    public function testWrong(): void
    {
        $email = 'test-email@test.com';
        $password = 'Aa123456789';
        $wrongPassword = 'qwerty123456';

        $user = User::factory()->create([
            'email' => $email,
            'password' => $password,
            'phone_auth' => false,
            'status' => User::STATUS_ACTIVE
        ]);

        $payload = [
            'email' => $email,
            'password' => $wrongPassword
        ];

        $response = $this->postJson($this->authUrl, $payload);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'Invalid login credentials.'
        ]);
    }
}
