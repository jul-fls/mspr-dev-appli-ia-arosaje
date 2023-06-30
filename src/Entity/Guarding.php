<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\GuardingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GuardingRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => 'guarding:item']),
        new GetCollection(normalizationContext: ['groups' => 'guarding:list']),
        new Post(normalizationContext: ['groups' => 'guarding:item']),
        new Put(normalizationContext: ['groups' => 'guarding:item']),
        new Delete(normalizationContext: ['groups' => 'guarding:item']),
        new Patch(normalizationContext: ['groups' => 'guarding:item']),
    ],
    order: ['from_timestamp' => 'DESC', 'guardian' => 'ASC'],
    paginationEnabled: false,
)]
class Guarding
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['guarding:list', 'guarding:item'])]
    public ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    public ?Plant $plant = null;

    #[ORM\ManyToOne(inversedBy: 'guardings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['guarding:list', 'guarding:item'])]
    public ?User $guardian = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['guarding:list', 'guarding:item'])]
    public ?\DateTimeInterface $from_timestamp = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['guarding:list', 'guarding:item'])]
    public ?\DateTimeInterface $to_timestamp = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPlant(): ?Plant
    {
        return $this->plant;
    }

    public function setPlant(?Plant $plant): self
    {
        $this->plant = $plant;

        return $this;
    }

    public function getGuardian(): ?User
    {
        return $this->guardian;
    }

    public function setGuardian(?User $guardian): self
    {
        $this->guardian = $guardian;

        return $this;
    }

    public function getFromTimestamp(): ?\DateTimeInterface
    {
        return $this->from_timestamp;
    }

    public function setFromTimestamp(\DateTimeInterface $from_timestamp): self
    {
        $this->from_timestamp = $from_timestamp;

        return $this;
    }

    public function getToTimestamp(): ?\DateTimeInterface
    {
        return $this->to_timestamp;
    }

    public function setToTimestamp(?\DateTimeInterface $to_timestamp): self
    {
        $this->to_timestamp = $to_timestamp;

        return $this;
    }
}
