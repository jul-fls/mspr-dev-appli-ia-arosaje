<?php

namespace App\Entity;

use App\Repository\GuardingRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GuardingRepository::class)]
class Guarding
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    public ?Plant $plant = null;

    #[ORM\ManyToOne(inversedBy: 'guardings')]
    #[ORM\JoinColumn(nullable: false)]
    public ?User $guardian = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    public ?\DateTimeInterface $from_timestamp = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
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
