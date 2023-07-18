<?php

namespace App\Tests\Entity;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class RoleTest extends TestCase
{
    public function testRole(): void
    {
        // Create some dummy data
        $roleName = 'Admin';
        $powerLevel = 10;
        $user = $this->createMock(User::class);

        $role = new Role();

        // Verify that the getters initially return null or default values
        $this->assertNull($role->getName());
        $this->assertNull($role->getPowerLevel());
        $this->assertInstanceOf(ArrayCollection::class, $role->getUsers());

        // Set dummy data
        $role->setName($roleName);
        $role->setPowerLevel($powerLevel);
        $role->addUser($user);

        // Verify that the setters have worked correctly
        $this->assertSame($roleName, $role->getName());
        $this->assertSame($powerLevel, $role->getPowerLevel());
        $this->assertTrue($role->getUsers()->contains($user));

        // Remove user and check
        $role->removeUser($user);
        $this->assertFalse($role->getUsers()->contains($user));
    }
}
