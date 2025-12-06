<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Email
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $recipient;

    #[ORM\Column(length: 255)]
    private string $subject;

    #[ORM\Column(type: 'text')]
    private string $body;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $scheduledAt;

    #[ORM\Column(length: 20)]
    private string $status = 'pending';


    public function getId(): ?int
    {
        return $this->id;
    }
    public function getRecipient(): string
    {
        return $this->recipient;
    }
    public function setRecipient(string $recipient): static
    {
        $this->recipient = $recipient;
        return $this;
    }
    public function getSubject(): string
    {
        return $this->subject;
    }
    public function setSubject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }
    public function getBody(): string
    {
        return $this->body;
    }
    public function setBody(string $body): static
    {
        $this->body = $body;
        return $this;
    }
    public function getScheduledAt(): \DateTimeInterface
    {
        return $this->scheduledAt;
    }
    public function setScheduledAt(\DateTimeInterface $scheduledAt): static
    {
        $this->scheduledAt = $scheduledAt;
        return $this;
    }
    public function getStatus(): string
    {
        return $this->status;
    }
    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }
}
