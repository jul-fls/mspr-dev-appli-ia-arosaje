<?php

namespace App\Tests\Entity;

use App\Entity\Guarding;
use App\Entity\User;
use App\Entity\Plant;
use PHPUnit\Framework\TestCase;

class GuardingTest extends TestCase
{
    public function testGuarding(): void
    {
        // Create some dummy data
        $plant = $this->createMock(Plant::class);
        $guardian = $this->createMock(User::class);
        $fromTimestamp = new \DateTime();
        $toTimestamp = new \DateTime();

        $guarding = new Guarding();

        // Verify that the getters initially return null
        $this->assertNull($guarding->getPlant());
        $this->assertNull($guarding->getGuardian());
        $this->assertNull($guarding->getFromTimestamp());
        $this->assertNull($guarding->getToTimestamp());

        // Set dummy data
        $guarding->setPlant($plant);
        $guarding->setGuardian($guardian);
        $guarding->setFromTimestamp($fromTimestamp);
        $guarding->setToTimestamp($toTimestamp);

        // Verify that the setters have worked correctly
        $this->assertSame($plant, $guarding->getPlant());
        $this->assertSame($guardian, $guarding->getGuardian());
        $this->assertSame($fromTimestamp, $guarding->getFromTimestamp());
        $this->assertSame($toTimestamp, $guarding->getToTimestamp());
    }
}
