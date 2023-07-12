<?php

namespace Tests\Feature\Api\v1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LogoutOtherDevicesTest extends TestCase
{
    private $logoutUrl = '/api/v1/auth/logout-other-devices';

    /**
     * Method testSuccessLogoutOtherDevices
     *
     * @return void
     */
    public function testSuccessLogoutOtherDevices()
    {
        $user = User::factory()->create();

        $firstToken = $user->createToken('first-test-token')->plainTextToken;
        $secondToken = $user->createToken('second-test-token')->plainTextToken;
        $thirdToken = $user->createToken('third-test-token')->plainTextToken;

        $headers = ['Authorization' => 'Bearer ' . $firstToken];

        $response = $this->postJson($this->logoutUrl, [], $headers);

        $response->assertStatus(202)
            ->assertJson([
                'message' => 'Other devices have been logged out.'
            ]);

        // Ensure the access token is revoked
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'token' => hash('sha256', $secondToken),
            'revoked' => false,
        ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'token' => hash('sha256', $thirdToken),
            'revoked' => false,
        ]);
    }
    
    /**
     * Method testWrongLogoutOtherDevices
     *
     * @return void
     */
    public function testWrongLogoutOtherDevices()
    {
        $user = User::factory()->create();

        $firstToken = $user->createToken('first-test-token')->plainTextToken;
        $secondToken = $user->createToken('second-test-token')->plainTextToken;
        $thirdToken = $user->createToken('third-test-token')->plainTextToken;
        
        $wrongToken = 'wrongToken';
        $headers = ['Authorization' => 'Bearer ' . $wrongToken];

        $response = $this->postJson($this->logoutUrl, [], $headers);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }
}
