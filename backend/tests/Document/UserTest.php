<?php

namespace App\Tests\Document;

use App\Document\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testEmailIsLowercased(): void
    {
        $user = new User();
        $user->setEmail('Test@Example.COM');

        $this->assertSame('test@example.com', $user->getEmail());
    }

    public function testRoleUserIsAlwaysPresent(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);

        $roles = $user->getRoles();

        $this->assertContains('ROLE_USER', $roles);
        $this->assertContains('ROLE_ADMIN', $roles);
        $this->assertCount(2, array_unique($roles));
    }
}
