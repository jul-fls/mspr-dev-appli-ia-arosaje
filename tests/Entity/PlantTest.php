<?php

namespace App\Tests\Entity;

use App\Entity\Plant;
use App\Entity\User;
use App\Entity\Conversation;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class PlantTest extends TestCase
{
    public function testPlant(): void
    {
        // Create some dummy data
        $plantName = 'Rose';
        $scientificName = 'Rosa';
        $familyName = 'Rosaceae';
        $image = base64_encode('dummyImage');
        $environment = true;
        $gbifId = 123456;
        $isPublished = true;
        $owner = $this->createMock(User::class);
        $description = 'A beautiful rose plant';
        $birth = 1995;
        $conversation = $this->createMock(Conversation::class);

        $plant = new Plant();

        // Verify that the getters initially return null or default values
        $this->assertNull($plant->getPlantName());
        $this->assertNull($plant->getScientificName());
        $this->assertNull($plant->getFamilyName());
        $this->assertNull($plant->getImage());
        $this->assertNull($plant->isEnvironment());
        $this->assertNull($plant->getGbifId());
        $this->assertNull($plant->isIsPublished());
        $this->assertNull($plant->getOwner());
        $this->assertNull($plant->getDescription());
        $this->assertNull($plant->getBirth());
        $this->assertInstanceOf(ArrayCollection::class, $plant->getConversations());

        // Set dummy data
        $plant->setPlantName($plantName);
        $plant->setScientificName($scientificName);
        $plant->setFamilyName($familyName);
        $plant->setImage($image);
        $plant->setEnvironment($environment);
        $plant->setGbifId($gbifId);
        $plant->setIsPublished($isPublished);
        $plant->setOwner($owner);
        $plant->setDescription($description);
        $plant->setBirth($birth);
        $plant->addConversation($conversation);

        // Verify that the setters have worked correctly
        $this->assertSame($plantName, $plant->getPlantName());
        $this->assertSame($scientificName, $plant->getScientificName());
        $this->assertSame($familyName, $plant->getFamilyName());
        $this->assertSame($image, $plant->getImage());
        $this->assertSame($environment, $plant->isEnvironment());
        $this->assertSame($gbifId, $plant->getGbifId());
        $this->assertSame($isPublished, $plant->isIsPublished());
        $this->assertSame($owner, $plant->getOwner());
        $this->assertSame($description, $plant->getDescription());
        $this->assertSame($birth, $plant->getBirth());
        $this->assertTrue($plant->getConversations()->contains($conversation));

        // Remove conversation and check
        $plant->removeConversation($conversation);
        $this->assertFalse($plant->getConversations()->contains($conversation));
    }
}
