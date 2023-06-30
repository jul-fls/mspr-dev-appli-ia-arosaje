<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\PlantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: PlantRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => 'plant:item']),
        new GetCollection(normalizationContext: ['groups' => 'plant:list']),
        new Post(normalizationContext: ['groups' => 'plant:item']),
        new Put(normalizationContext: ['groups' => 'plant:item']),
        new Delete(normalizationContext: ['groups' => 'plant:item']),
        new Patch(normalizationContext: ['groups' => 'plant:item']),
    ],
    order: ['plant_name' => 'DESC', 'birth' => 'ASC'],
    paginationEnabled: false,
)]
class Plant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['plant:list', 'plant:item'])]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['plant:list', 'plant:item'])]
    public ?string $plant_name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['plant:list', 'plant:item'])]
    public ?string $scientific_name = null;

    #[ORM\Column(length: 255)]
    #[Groups(['plant:list', 'plant:item'])]
    public ?string $family_name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['plant:list', 'plant:item'])]
    public ?string $image = null;

    #[ORM\Column]
    #[Groups(['plant:list', 'plant:item'])]
    public ?bool $environment = null;

    #[ORM\Column]
    #[Groups(['plant:list', 'plant:item'])]
    public ?int $gbif_id = null;

    #[ORM\Column]
    #[Groups(['plant:list', 'plant:item'])]
    public ?bool $is_published = null;

    #[ORM\ManyToOne(inversedBy: 'plants')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['plant:list', 'plant:item'])]
    public ?User $owner = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['plant:list', 'plant:item'])]
    public ?string $description = null;

    #[ORM\Column]
    #[Groups(['plant:list', 'plant:item'])]
    public ?int $birth = null;

    #[ORM\OneToMany(mappedBy: 'plant_id', targetEntity: Conversation::class, orphanRemoval: true)]
    private Collection $conversations;

    public function __construct()
    {
        $this->conversations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlantName(): ?string
    {
        return $this->plant_name;
    }

    public function setPlantName(string $plant_name): self
    {
        $this->plant_name = $plant_name;

        return $this;
    }

    public function getScientificName(): ?string
    {
        return $this->scientific_name;
    }

    public function setScientificName(string $scientific_name): self
    {
        $this->scientific_name = $scientific_name;

        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->family_name;
    }

    public function setFamilyName(string $family_name): self
    {
        $this->family_name = $family_name;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function isEnvironment(): ?bool
    {
        return $this->environment;
    }

    public function setEnvironment(bool $environment): self
    {
        $this->environment = $environment;

        return $this;
    }

    public function getGbifId(): ?int
    {
        return $this->gbif_id;
    }

    public function setGbifId(int $gbif_id): self
    {
        $this->gbif_id = $gbif_id;

        return $this;
    }

    public function isIsPublished(): ?bool
    {
        return $this->is_published;
    }

    public function setIsPublished(bool $is_published): self
    {
        $this->is_published = $is_published;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function __toString(): string
    {
        // Replace 'username' with the appropriate property of the User class.
        // This should be a string property that can represent the User object.
        return $this->plant_name.' '.$this->owner;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getBirth(): ?int
    {
        return $this->birth;
    }

    public function setBirth(int $birth): self
    {
        $this->birth = $birth;

        return $this;
    }

    /**
     * @return Collection<int, Conversation>
     */
    public function getConversations(): Collection
    {
        return $this->conversations;
    }

    public function addConversation(Conversation $conversation): self
    {
        if (!$this->conversations->contains($conversation)) {
            $this->conversations->add($conversation);
            $conversation->setPlantId($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): self
    {
        if ($this->conversations->removeElement($conversation)) {
            // set the owning side to null (unless already changed)
            if ($conversation->getPlantId() === $this) {
                $conversation->setPlantId(null);
            }
        }

        return $this;
    }
}
