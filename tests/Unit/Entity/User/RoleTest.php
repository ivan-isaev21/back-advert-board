<?php

namespace Tests\Unit\Entity\User;

use App\Models\User;
use Tests\TestCase;

class RoleTest extends TestCase
{
    /**
     * Method testChange
     *
     * @return void
     */
    public function testChange(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_USER
        ]);

        $this->assertFalse($user->isAdmin());

        $user->changeRole(User::ROLE_ADMIN);

        self::assertTrue($user->isAdmin());
    }

    /**
     * Method testAlready
     *
     * @return void
     */
    public function testAlready(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ADMIN
        ]);

        $this->expectExceptionMessage('Role is already assigned.');

        $user->changeRole(User::ROLE_ADMIN);
    }
}
