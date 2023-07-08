<?php

namespace Tests\Unit;

use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class PhoneTest extends TestCase
{
    /**
     * Method test_default
     *
     * @return void
     */
    public function testDefault()
    {
        $user = User::factory()->create([
            'phone' => null,
            'phone_verified' => false,
            'phone_verify_token' => null,
        ]);

        $this->assertFalse($user->isPhoneVerified());
    }

    /**
     * Method test_request_empty_phone
     *
     * @return void
     */
    public function testRequestEmptyPhone()
    {
        $user = User::factory()->create([
            'phone' => null,
            'phone_verified' => false,
            'phone_verify_token' => null,
        ]);

        $this->expectExceptionMessage('Phone number is empty.');
        $user->requestPhoneVerification(Carbon::now());
    }

    /**
     * Method test_request
     *
     * @return void
     */
    public function testRequest()
    {
        $user = User::factory()->create([
            'phone' => '+380000000000',
            'phone_verified' => false,
            'phone_verify_token' => null,
        ]);

        $token = $user->requestPhoneVerification(Carbon::now());

        $this->assertFalse($user->isPhoneVerified());
        $this->assertNotEmpty($token);
    }

    /**
     * Method testRequestWithOldPhone
     *
     * @return void
     */
    public function testRequestWithOldPhone()
    {
        $user = User::factory()->create([
            'phone' => '+380000000000',
            'phone_verified' => true,
            'phone_verify_token' => null,
        ]);


        $this->assertTrue($user->isPhoneVerified());

        $user->requestPhoneVerification(Carbon::now());

        $this->assertFalse($user->isPhoneVerified());
        $this->assertNotEmpty($user->phone_verify_token);
    }

    /**
     * Method testRequestAlreadySentTimeout
     *
     * @return void
     */
    public function testRequestAlreadySentTimeout()
    {
        $user = User::factory()->create([
            'phone' => '+380000000000',
            'phone_verified' => true,
            'phone_verify_token' => null,
        ]);

        $user->requestPhoneVerification($now = Carbon::now());
        $user->requestPhoneVerification($now->copy()->addSeconds(500));

        self::assertFalse($user->isPhoneVerified());
    }

    /**
     * Method testRequestAlreadySent
     *
     * @return void
     */
    public function testRequestAlreadySent()
    {
        $user = User::factory()->create([
            'phone' => '+380000000000',
            'phone_verified' => true,
            'phone_verify_token' => null,
        ]);

        $user->requestPhoneVerification($now = Carbon::now());

        $this->expectExceptionMessage('Token is already requested.');
        $user->requestPhoneVerification($now->copy()->addSeconds(15));
    }

    /**
     * Method testVerify
     *
     * @return void
     */
    public function testVerify()
    {
        $user = User::factory()->create([
            'phone' => '+380000000000',
            'phone_verified' => false,
            'phone_verify_token' => $token = 'token',
            'phone_verify_token_expire' => $now = Carbon::now()
        ]);

        $this->assertFalse($user->isPhoneVerified());

        $user->verifyPhone($token, $now->copy()->subSeconds(15));

        $this->assertTrue($user->isPhoneVerified());
    }

    /**
     * Method testVerifyIncorrectToken
     *
     * @return void
     */
    public function testVerifyIncorrectToken()
    {
        $user = User::factory()->create([
            'phone' => '+380000000000',
            'phone_verified' => false,
            'phone_verify_token' => $token = 'token',
            'phone_verify_token_expire' => $now = Carbon::now()
        ]);

        $this->expectExceptionMessage('Incorrect verify token.');
        $user->verifyPhone('other_token', $now->copy()->subSeconds(15));
    }

    public function testVerifyExpiredToken()
    {
        $user = User::factory()->create([
            'phone' => '+380000000000',
            'phone_verified' => false,
            'phone_verify_token' => $token = 'token',
            'phone_verify_token_expire' => $now = Carbon::now()
        ]);

        $this->expectExceptionMessage('Token is expired.');
        $user->verifyPhone($token, $now->copy()->addSeconds(500));
    }
}
