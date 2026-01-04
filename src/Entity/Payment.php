<?php

namespace App\Entity;

use App\Repository\PaymentRepository;
use App\Entity\Ticket;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\OneToMany(mappedBy: 'payment', targetEntity: Ticket::class, cascade: ['persist'])]
    private Collection $tickets;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->tickets = new ArrayCollection();
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

        // Simulation de traitement de paiement plus sécurisée
        // Utilise un hash déterministe basé sur des critères réels pour une simulation cohérente
        // En production, remplacer par un vrai gateway (Stripe, PayPal, etc.)
        $paymentData = sprintf(
            '%s-%s-%s-%s',
            $this->getId() ?? uniqid(),
            $this->getAmount(),
            $this->getMethode(),
            $this->getCreatedAt()?->getTimestamp()
        );
        
        // Génère un hash et détermine le succès (95% de taux de succès simulé)
        $hash = hash('sha256', $paymentData);
        $hashValue = hexdec(substr($hash, 0, 8));
        $success = ($hashValue % 100) < 95;

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

        // Simulation de remboursement plus sécurisée (90% de taux de succès simulé)
        $refundData = sprintf(
            '%s-refund-%s-%s',
            $this->getId(),
            $this->getAmount(),
            $this->getCreatedAt()?->getTimestamp()
        );
        
        $hash = hash('sha256', $refundData);
        $hashValue = hexdec(substr($hash, 0, 8));
        $success = ($hashValue % 100) < 90;

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

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): static
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setPayment($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): static
    {
        if ($this->tickets->removeElement($ticket)) {
            if ($ticket->getPayment() === $this) {
                $ticket->setPayment(null);
            }
        }

        return $this;
    }
}
