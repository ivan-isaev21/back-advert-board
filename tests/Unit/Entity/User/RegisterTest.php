<?php

namespace Tests\Unit\Entity\User;

use App\Models\User;
use Tests\TestCase;

class RegisterTest extends TestCase
{

    /**
     * Method test_register
     *
     * @return void
     */
    public function testRegister(): void
    {
        $user = User::register(
            $name = 'name',
            $email = 'email',
            $password = 'password'
        );

        $this->assertNotEmpty($user);
        $this->assertEquals($name, $user->name);
        $this->assertEquals($email, $user->email);
        $this->assertNotEmpty($user->password);
        $this->assertNotEquals($password, $user->password);

        $this->assertTrue($user->isWait());
        $this->assertFalse($user->isActive());
        $this->assertFalse($user->isAdmin());
    }

    /**
     * Method test_verify
     *
     * @return void
     */
    public function testVerify()
    {
        $user = User::register('name', 'email', 'password');
        $user->verify();
        $this->assertFalse($user->isWait());
        $this->assertTrue($user->isActive());
    }

    /**
     * Method test_already_verified
     *
     * @return void
     */
    public function testAlreadyVerified()
    {
        $user = User::register('name', 'email', 'password');
        $user->verify();
        $this->expectExceptionMessage('User is already verified.');
        $user->verify();
    }
}
