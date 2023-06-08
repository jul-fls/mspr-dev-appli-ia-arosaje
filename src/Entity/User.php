<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Nullable;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 255, nullable: false)]
    public ?string $password = null;

    #[ORM\Column(length: 255, nullable: false)]
    public ?string $email = null;

    #[ORM\Column(length: 10, nullable: true)]
    public ?string $phone = null;

    #[ORM\Column(nullable: true)]
    public ?int $address_number = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $address_street = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $address_city = null;

    #[ORM\Column(length: 5, nullable: true)]
    public ?string $address_zipcode = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $address_country = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: Plant::class, orphanRemoval: true)]
    public Collection $plants;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: true)]
    public ?Role $role = null;

    #[ORM\OneToMany(mappedBy: 'guardian', targetEntity: Guarding::class, orphanRemoval: true)]
    public Collection $guardings;

    #[ORM\Column(length: 255,nullable: false)]
    public ?string $first_name = null;

    #[ORM\Column(length: 255,nullable: false)]
    public ?string $last_name = null;

    public function __construct()
    {
        $this->plants = new ArrayCollection();
        $this->guardings = new ArrayCollection();
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getAddressNumber(): ?int
    {
        return $this->address_number;
    }

    public function setAddressNumber(?int $address_number): self
    {
        $this->address_number = $address_number;

        return $this;
    }

    public function getAddressStreet(): ?string
    {
        return $this->address_street;
    }

    public function setAddressStreet(?string $address_street): self
    {
        $this->address_street = $address_street;

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
}
