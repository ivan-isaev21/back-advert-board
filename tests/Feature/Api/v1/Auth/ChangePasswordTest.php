<?php

namespace Tests\Feature\Api\v1\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    private $changePasswordUrl = '/api/v1/auth/change-password';

    /**
     * Method testEmptyPayload
     *
     * @return void
     */
    public function testEmptyPayload(): void
    {
        $response = $this->postJson($this->changePasswordUrl);
        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The email field is required. (and 2 more errors)',
            'errors' => [
                'email' => ['The email field is required.'],
                'token' => ['The token field is required.'],
                'password' => ['The password field is required.'],
            ]
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
        $newPassword = 'qwerty12345';

        $user = User::factory()->create([
            'email' => $email,
            'password' => $password,
            'phone_auth' => false,
            'status' => User::STATUS_ACTIVE
        ]);

        $token = $user->requestPasswordResetToken(Carbon::now());

        $payload = [
            'email' => $email,
            'token' => $token,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ];
        $response = $this->postJson($this->changePasswordUrl, $payload);
        $response->assertStatus(202);
        $response->assertJson([
            'message' => 'Password success changed.'
        ]);
    }
}
