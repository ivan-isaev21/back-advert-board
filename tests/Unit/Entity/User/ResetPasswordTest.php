<?php

namespace Tests\Unit\Entity\User;

use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    /**
     * Method testRequestPasswordResetTokenAlreadySent
     *
     * @return void
     */
    public function testRequestPasswordResetTokenAlreadySent(): void
    {
        $now = Carbon::now();

        $user = User::factory()->create([
            'password_reset_token' => null,
            'password_reset_token_expire' => null,
        ]);

        $user->requestPasswordResetToken($now->copy()->addHour());

        $this->assertNotEmpty($user->password_reset_token);
        $this->assertNotEmpty($user->password_reset_token_expire);

        $this->expectExceptionMessage('Password reset token is already requested.');
        $user->requestPasswordResetToken($now);
    }

    /**
     * Method testChangePasswordByToken
     *
     * @return void
     */
    public function testChangePasswordByToken(): void
    {
        $passwordResetToken = '12345';
        $wrongPasswordResetToken = $passwordResetToken . '43';
        $now = Carbon::now();
        $password = 'Aa123456789';
        $newPassword = 'qwerty123456';

        $user = User::factory()->create([
            'password' => $password,
            'password_reset_token' => $passwordResetToken,
            'password_reset_token_expire' => $now->copy()->addHour()
        ]);

        $this->expectExceptionMessage('Incorrect password reset token.');
        $user->changePasswordByToken($wrongPasswordResetToken, $newPassword, $now);

        $this->expectExceptionMessage('Password reset token is expired.');
        $user->changePasswordByToken($passwordResetToken, $newPassword, $now->copy()->addHours(2));
    }
}
