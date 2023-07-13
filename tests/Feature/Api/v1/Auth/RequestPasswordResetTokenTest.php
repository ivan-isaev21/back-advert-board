<?php

namespace Tests\Feature\Api\v1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RequestPasswordResetTokenTest extends TestCase
{
    private $requestPasswordResetTokenUrl = '/api/v1/auth/request-password-reset-token';

    /**
     * Method testEmptyPayload
     *
     * @return void
     */
    public function testEmptyPayload(): void
    {
        $response = $this->postJson($this->requestPasswordResetTokenUrl);
        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'The email field is required.',
            'errors' => [
                'email' => ['The email field is required.']
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
        $payload = ['email' => 'test-email@test.com'];
        $response = $this->postJson($this->requestPasswordResetTokenUrl, $payload);
        $response->assertStatus(202);
        $response->assertJson([
            'message' => 'Check your email and click on the link to reset password.'
        ]);
    }
}
