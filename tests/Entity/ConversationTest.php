<?php

namespace App\Tests\Entity;

use App\Entity\Conversation;
use App\Entity\User;
use App\Entity\Plant;
use PHPUnit\Framework\TestCase;

class ConversationTest extends TestCase
{
    public function testConversation(): void
    {
        // Define some dummy data
        $toUser = $this->createMock(User::class);
        $fromUser = $this->createMock(User::class);
        $plant = $this->createMock(Plant::class);

        $conversation = new Conversation();

        // Verify that the getters initially return null
        $this->assertNull($conversation->getToUser());
        $this->assertNull($conversation->getFromUser());
        $this->assertNull($conversation->getPlantId());

        // Set dummy data
        $conversation->setToUser($toUser);
        $conversation->setFromUser($fromUser);
        $conversation->setPlantId($plant);

        // Verify that the setters have worked correctly
        $this->assertSame($toUser, $conversation->getToUser());
        $this->assertSame($fromUser, $conversation->getFromUser());
        $this->assertSame($plant, $conversation->getPlantId());

        // Verify that the __toString() method returns the expected result
        $this->assertIsString($conversation->__toString());
    }
}
