<?php

namespace Tests\Feature\Api\v1\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VerifyPhoneTokenTest extends TestCase
{    
    /**
     * Method testSuccess
     *
     * @return void
     */
    public function testSuccess()
    {
        $phoneVerifyToken = '12345';

        $user = User::factory()->create([
            'phone' => '+380000000000',
            'phone_verified' => true,
            'phone_verify_token' => $phoneVerifyToken,
            'phone_verify_token_expire' => Carbon::now()->addHour(),
            'phone_auth' => true,
            'status' => User::STATUS_ACTIVE
        ]);

        $phoneVerifyTokenUrl = 'api/v1/auth/phone/verify/' . $user->id . '/' . $phoneVerifyToken;

        $payload = [
            'remember' => 1,
        ];

        $response = $this->postJson($phoneVerifyTokenUrl, $payload);
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
    public function testWrong()
    {
        $phoneVerifyToken = '12345';
        $wrongPhoneVerifyToken = 'qwerty12345';

        $user = User::factory()->create([
            'phone' => '+380000000000',
            'phone_verified' => true,
            'phone_verify_token' => $phoneVerifyToken,
            'phone_verify_token_expire' => Carbon::now()->addHour(),
            'phone_auth' => true,
            'status' => User::STATUS_ACTIVE
        ]);

        $phoneVerifyTokenUrl = 'api/v1/auth/phone/verify/' . $user->id . '/' . $wrongPhoneVerifyToken;

        $payload = [
            'remember' => 1,
        ];

        $response = $this->postJson($phoneVerifyTokenUrl, $payload);
        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'Invalid auth token.'
        ]);
    }
}
