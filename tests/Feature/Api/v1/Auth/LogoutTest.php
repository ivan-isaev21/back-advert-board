<?php

namespace Tests\Feature\Api\v1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    private $logoutUrl = '/api/v1/auth/logout';

    /**
     * Method testSuccessLogout
     *
     * @return void
     */
    public function testSuccessLogout()
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;
        $headers = ['Authorization' => 'Bearer ' . $token];

        $response = $this->postJson($this->logoutUrl, [], $headers);

        $response->assertStatus(202)
            ->assertJson([
                'message' => 'Successfully logged out.'
            ]);

        // Ensure the access token is revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'token' => hash('sha256', $token),
            'revoked' => false,
        ]);
    }

    /**
     * Method testWrongLogout
     *
     * @return void
     */
    public function testWrongLogout()
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;
        $wrongToken = 'wrongToken';
        $headers = ['Authorization' => 'Bearer ' . $wrongToken];

        $response = $this->postJson($this->logoutUrl, [], $headers);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }
}
