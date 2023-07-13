<?php

namespace Tests\Feature\Api\v1\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VerifyUserTest extends TestCase
{
    /**
     * Method testSuccess
     *
     * @return void
     */
    public function testSuccess()
    {
        $emailVerifyToken = '12345';

        $user = User::factory()->create([
            'verify_token' => $emailVerifyToken,
            'email_verified_at' => null,
            'phone_auth' => false,
            'status' => User::STATUS_WAIT
        ]);

        $emailVerifyTokenUrl = 'api/v1/auth/email/verify/' . $user->id . '/' . $emailVerifyToken;

        $response = $this->postJson($emailVerifyTokenUrl);
        $response->assertStatus(202);
        $response->assertJson([
            'message' => 'Successfully verify email.'
        ]);
    }
}
