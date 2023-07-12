<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => 'user:item']),
        new GetCollection(normalizationContext: ['groups' => 'user:list']),
        new Post(normalizationContext: ['groups' => 'user:item']),
        new Put(normalizationContext: ['groups' => 'user:item']),
        new Delete(normalizationContext: ['groups' => 'user:item']),
        new Patch(normalizationContext: ['groups' => 'user:item']),
    ],
    order: ['last_name' => 'DESC', 'id' => 'ASC'],
    paginationEnabled: false,
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:list', 'user:item'])]
    public ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Groups(['user:list', 'user:item'])]
    public ?string $password = null;

    #[ORM\Column(length: 255, nullable: false)]
    #[Groups(['user:list', 'user:item'])]
    public ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:list', 'user:item'])]
    public ?string $address_city = null;

    #[ORM\Column(length: 5, nullable: true)]
    #[Groups(['user:list', 'user:item'])]
    public ?string $address_zipcode = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:list', 'user:item'])]
    public ?string $address_country = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Plant::class, orphanRemoval: true)]
    public Collection $plants;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: true)]
    public ?Role $role = null;

    #[ORM\OneToMany(mappedBy: 'guardian', targetEntity: Guarding::class, orphanRemoval: true)]
    public Collection $guardings;

    #[ORM\Column(length: 255,nullable: false)]
    #[Groups(['user:list', 'user:item'])]
    public ?string $first_name = null;

    #[ORM\Column(length: 255,nullable: false)]
    #[Groups(['user:list', 'user:item'])]
    public ?string $last_name = null;

    #[ORM\OneToMany(mappedBy: 'to_user', targetEntity: Conversation::class, orphanRemoval: true)]
    private Collection $conversations;

    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Message::class, orphanRemoval: true)]
    private Collection $messages;

    public function __construct()
    {
        $this->plants = new ArrayCollection();
        $this->guardings = new ArrayCollection();
        $this->conversations = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAddressCity(): ?string
    {
        return $this->address_city;
    }

    public function setAddressCity(?string $address_city): self
    {
        $this->address_city = $address_city;

        return $this;
    }

    public function getAddressZipcode(): ?string
    {
        return $this->address_zipcode;
    }

    public function setAddressZipcode(?string $address_zipcode): self
    {
        $this->address_zipcode = $address_zipcode;

        return $this;
    }

    public function getAddressCountry(): ?string
    {
        return $this->address_country;
    }

    public function setAddressCountry(?string $address_country): self
    {
        $this->address_country = $address_country;

        return $this;
    }

    /**
     * @return Collection<int, Plant>
     */
    public function getPlants(): Collection
    {
        return $this->plants;
    }

    public function addPlant(Plant $plant): self
    {
        if (!$this->plants->contains($plant)) {
            $this->plants->add($plant);
            $plant->setOwner($this);
        }

        return $this;
    }

    public function removePlant(Plant $plant): self
    {
        if ($this->plants->removeElement($plant)) {
            // set the owning side to null (unless already changed)
            if ($plant->getOwner() === $this) {
                $plant->setOwner(null);
            }
        }

        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): self
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return Collection<int, Guarding>
     */
    public function getGuardings(): Collection
    {
        return $this->guardings;
    }

    public function addGuarding(Guarding $guarding): self
    {
        if (!$this->guardings->contains($guarding)) {
            $this->guardings->add($guarding);
            $guarding->setGuardian($this);
        }

        return $this;
    }

    public function removeGuarding(Guarding $guarding): self
    {
        if ($this->guardings->removeElement($guarding)) {
            // set the owning side to null (unless already changed)
            if ($guarding->getGuardian() === $this) {
                $guarding->setGuardian(null);
            }
        }

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }
    
    public function __toString(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getRoles(): array
    {
        return [$this->getRole()->getName()];
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
        
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
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
            $conversation->setToUser($this);
        }

        return $this;
    }

    public function removeConversation(Conversation $conversation): self
    {
        if ($this->conversations->removeElement($conversation)) {
            // set the owning side to null (unless already changed)
            if ($conversation->getToUser() === $this) {
                $conversation->setToUser(null);
            }
        }

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
            $message->setSender($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getSender() === $this) {
                $message->setSender(null);
            }
        }

        return $this;
    }
}
