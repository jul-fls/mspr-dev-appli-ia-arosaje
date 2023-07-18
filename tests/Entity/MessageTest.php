<?php

namespace App\Tests\Entity;

use App\Entity\Message;
use App\Entity\User;
use App\Entity\Conversation;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testMessage(): void
    {
        // Create some dummy data
        $content = 'Hello world';
        $sentAt = new \DateTimeImmutable();
        $viewAt = new \DateTimeImmutable();
        $replyToMessage = 1;
        $conversation = $this->createMock(Conversation::class);
        $sender = $this->createMock(User::class);

        $message = new Message();

        // Verify that the getters initially return null or default values
        $this->assertNull($message->getContent());
        $this->assertInstanceOf(\DateTimeImmutable::class, $message->getViewAt());
        $this->assertNull($message->getSentAt());
        $this->assertNull($message->getReplyToMessage());
        $this->assertNull($message->getConversation());
        $this->assertNull($message->getSender());

        // Set dummy data
        $message->setContent($content);
        $message->setSentAt($sentAt);
        $message->setViewAt($viewAt);
        $message->setReplyToMessage($replyToMessage);
        $message->setConversation($conversation);
        $message->setSender($sender);

        // Verify that the setters have worked correctly
        $this->assertSame($content, $message->getContent());
        $this->assertSame($sentAt, $message->getSentAt());
        $this->assertSame($viewAt, $message->getViewAt());
        $this->assertSame($replyToMessage, $message->getReplyToMessage());
        $this->assertSame($conversation, $message->getConversation());
        $this->assertSame($sender, $message->getSender());
    }
}
