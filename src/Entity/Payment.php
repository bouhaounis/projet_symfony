<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $amount = null;

    #[ORM\Column(length: 50)]
    private ?string $methode = null;

    #[ORM\Column(length: 50)]
    private ?string $status = 'pending';

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(targetEntity: Booking::class, inversedBy: 'payments')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Booking $booking = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function processPayment(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        if ($this->booking === null || $this->booking->getStatus() === 'cancelled') {
            $this->status = 'cancelled';
            return false;
        }

        $success = rand(1, 100) > 5;

        if ($success) {
            $this->status = 'completed';
            return true;
        } else {
            $this->status = 'failed';
            return false;
        }
    }

    public function refund(): bool
    {
        if ($this->status !== 'completed') {
            return false;
        }

        $now = new \DateTimeImmutable();
        $interval = $this->createdAt->diff($now);

        if ($interval->days > 30) {
            return false;
        }

        $success = rand(1, 100) > 10;

        if ($success) {
            $this->status = 'refunded';
            return true;
        }

        return false;
    }

    public function isProcessable(): bool
    {
        return $this->status === 'pending' &&
            $this->booking !== null &&
            $this->booking->getStatus() !== 'cancelled';
    }

    public function isRefundable(): bool
    {
        if ($this->status !== 'completed') {
            return false;
        }

        $now = new \DateTimeImmutable();
        $interval = $this->createdAt->diff($now);
        return $interval->days <= 30;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = $amount;
        return $this;
    }

    public function getMethode(): ?string
    {
        return $this->methode;
    }

    public function setMethode(string $methode): static
    {
        $this->methode = $methode;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getBooking(): ?Booking
    {
        return $this->booking;
    }

    public function setBooking(?Booking $booking): static
    {
        $this->booking = $booking;
        return $this;
    }
}
