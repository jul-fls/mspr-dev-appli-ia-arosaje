<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RoleRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => 'role:item']),
        new GetCollection(normalizationContext: ['groups' => 'role:list']),
        new Post(normalizationContext: ['groups' => 'role:item']),
        new Put(normalizationContext: ['groups' => 'role:item']),
        new Delete(normalizationContext: ['groups' => 'role:item']),
        new Patch(normalizationContext: ['groups' => 'role:item']),
    ],
    order: ['name' => 'DESC', 'power_level' => 'ASC'],
    paginationEnabled: false,
)]
#[ApiResource(normalizationContext: ['groups' => ['role:list', 'role:item']])]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['role:list', 'role:item'])]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['role:list', 'role:item'])]
    public ?string $name = null;

    #[ORM\Column]
    #[Groups(['role:list', 'role:item'])]
    public ?int $power_level = null;

    #[ORM\OneToMany(mappedBy: 'role', targetEntity: User::class)]
    #[Groups(['role:list', 'role:item','user:item','user:list'])]
    public Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPowerLevel(): ?int
    {
        return $this->power_level;
    }

    public function setPowerLevel(int $power_level): self
    {
        $this->power_level = $power_level;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setRole($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getRole() === $this) {
                $user->setRole(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        // Replace 'name' with the appropriate property of the Role class.
        // This should be a string property that can represent the Role object.
        return $this->name;
    }

}
