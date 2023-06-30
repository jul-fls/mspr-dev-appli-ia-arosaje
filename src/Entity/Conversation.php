<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\ConversationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => 'conversation:item']),
        new GetCollection(normalizationContext: ['groups' => 'conversation:list']),
        new Post(normalizationContext: ['groups' => 'conversation:item']),
        new Put(normalizationContext: ['groups' => 'conversation:item']),
        new Delete(normalizationContext: ['groups' => 'conversation:item']),
        new Patch(normalizationContext: ['groups' => 'conversation:item']),
    ],
    order: ['plant_id' => 'DESC'],
    paginationEnabled: false,
)]
#[ApiResource(normalizationContext: ['groups' => ['conversation:list', 'conversation:item']])]
class Conversation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['conversation:list', 'conversation:item'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'conversations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['conversation:list', 'conversation:item'])]
    private ?User $to_user = null;

    #[ORM\ManyToOne(inversedBy: 'conversations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['conversation:list', 'conversation:item'])]
    private ?User $from_user = null;

    #[ORM\ManyToOne(inversedBy: 'conversations')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['conversation:list', 'conversation:item'])]
    private ?Plant $plant_id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getToUser(): ?User
    {
        return $this->to_user;
    }

    public function setToUser(?User $to_user): self
    {
        $this->to_user = $to_user;

        return $this;
    }

    public function getFromUser(): ?User
    {
        return $this->from_user;
    }

    public function setFromUser(?User $from_user): self
    {
        $this->from_user = $from_user;

        return $this;
    }

    public function getPlantId(): ?Plant
    {
        return $this->plant_id;
    }

    public function setPlantId(?Plant $plant_id): self
    {
        $this->plant_id = $plant_id;

        return $this;
    }
}
