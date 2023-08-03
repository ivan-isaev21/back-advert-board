<?php

namespace Tests\Unit\Entity\User;

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
     * Method test_request
     *
     * @return void
     */
    public function testRequest()
    {
        $user = User::factory()->create([
            'phone_verified' => false,
            'phone_verify_token' => null,
        ]);

        $phone = '+380000000000';

        $token = $user->requestPhoneVerification($phone, Carbon::now());

        $this->assertFalse($user->isPhoneVerified());
        $this->assertNotEmpty($token);
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

        $newPhone = '+380999000000';

        $user->requestPhoneVerification($newPhone, $now = Carbon::now());
        $user->requestPhoneVerification($newPhone, $now->copy()->addSeconds(500));

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
        $newPhone = '+380999000000';
        $user->requestPhoneVerification($newPhone, $now = Carbon::now());

        $this->expectExceptionMessage('Token is already requested.');
        $user->requestPhoneVerification($newPhone, $now->copy()->addSeconds(15));
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

    /**
     * Method testVerifyExpiredToken
     *
     * @return void
     */
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

    /**
     * Method testRequestPhoneVerifyToken
     *
     * @return void
     */
    public function testRequestPhoneVerifyToken(): void
    {

        $user = User::factory()->create([
            'phone' => null,
            'phone_verified' => false,
            'phone_verify_token' => null,
            'phone_verify_token_expire' => null
        ]);

        $this->expectExceptionMessage('Phone number is empty.');
        $user->requestPhoneVerifyToken($now = Carbon::now());

        $user->update(['phone' => '+380000000000', 'phone_auth' => true]);
        $user->requestPhoneVerifyToken($now);
        $this->expectExceptionMessage('Token is already requested.');
        $user->requestPhoneVerifyToken($now->copy()->addSeconds(50));

        $user->update(['phone_auth' => false]);
        $this->expectExceptionMessage('Phone auth is disabled.');
        $user->requestPhoneVerifyToken($now);
    }
}
