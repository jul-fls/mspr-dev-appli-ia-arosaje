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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\ManyToOne(inversedBy: 'conversationsTo',targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['conversation:list', 'conversation:item'])]
    public ?User $to_user = null;

    #[ORM\ManyToOne(inversedBy: 'conversationsFrom',targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['conversation:list', 'conversation:item'])]
    public ?User $from_user = null;

    #[ORM\ManyToOne(inversedBy: 'conversations',targetEntity: Plant::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['conversation:list', 'conversation:item'])]
    public ?Plant $plant_id = null;

    #[ORM\OneToMany(mappedBy: 'conversation', targetEntity: Message::class, orphanRemoval: true)]
    private Collection $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setConversation($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getConversation() === $this) {
                $message->setConversation(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->id . ' ' . $this->to_user . ' ' . $this->from_user . ' ' . $this->plant_id;
    }
}
