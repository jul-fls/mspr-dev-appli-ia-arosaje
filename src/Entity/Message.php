<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => 'message:item']),
        new GetCollection(normalizationContext: ['groups' => 'message:list']),
        new Post(normalizationContext: ['groups' => 'message:item']),
        new Put(normalizationContext: ['groups' => 'message:item']),
        new Delete(normalizationContext: ['groups' => 'message:item']),
        new Patch(normalizationContext: ['groups' => 'message:item']),
    ],
    order: ['sent_at' => 'DESC'],
    paginationEnabled: false,
)]
#[ApiResource(normalizationContext: ['groups' => ['message:list', 'message:item']])]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['message:list', 'message:item'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['message:list', 'message:item'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['message:list', 'message:item'])]
    private ?\DateTimeImmutable $sent_at = null;

    #[ORM\Column]
    #[Groups(['message:list', 'message:item'])]
    private ?\DateTimeImmutable $view_at = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['message:list', 'message:item'])]
    private ?int $reply_to_message = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getSentAt(): ?\DateTimeImmutable
    {
        return $this->sent_at;
    }

    public function setSentAt(\DateTimeImmutable $sent_at): self
    {
        $this->sent_at = $sent_at;

        return $this;
    }

    public function getViewAt(): ?\DateTimeImmutable
    {
        return $this->view_at;
    }

    public function setViewAt(\DateTimeImmutable $view_at): self
    {
        $this->view_at = $view_at;

        return $this;
    }

    public function getReplyToMessage(): ?int
    {
        return $this->reply_to_message;
    }

    public function setReplyToMessage(?int $reply_to_message): self
    {
        $this->reply_to_message = $reply_to_message;

        return $this;
    }
}
