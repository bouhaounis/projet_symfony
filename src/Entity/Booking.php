<?php

namespace App\Entity;

use App\Repository\BookingRepository;
use App\Entity\Event;
use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?float $total = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Event $event = null;

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToMany(targetEntity: Payment::class, mappedBy: 'booking', cascade: ['persist', 'remove'])]
    private Collection $payments;

    #[ORM\OneToMany(mappedBy: 'booking', targetEntity: Ticket::class, cascade: ['persist', 'remove'])]
    private Collection $tickets;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->status = 'pending';
        $this->payments = new ArrayCollection();
        $this->tickets = new ArrayCollection();
        $this->calculateTotal();
    }

    public function createBooking(): void
    {
        $this->status = 'confirmed';
        $this->createdAt = new \DateTime();
        $this->calculateTotal();
    }

    public function cancelBooking(): void
    {
        $this->status = 'cancelled';

        foreach ($this->payments as $payment) {
            if ($payment->getStatus() === 'pending') {
                $payment->setStatus('cancelled');
            }
        }
    }

    public function calculateTotal(): float
    {
        $pricePerUnit = $this->event?->getPrix() ?? 10.0;
        $quantity = $this->quantity ?? 0;
        $this->total = $quantity * $pricePerUnit;
        return $this->total;
    }

    public function getTotalPaid(): float
    {
        $totalPaid = 0;
        foreach ($this->payments as $payment) {
            if ($payment->getStatus() === 'completed') {
                $totalPaid += $payment->getAmount();
            }
        }
        return $totalPaid;
    }

    public function getRemainingBalance(): float
    {
        return max(0, $this->total - $this->getTotalPaid());
    }

    public function isFullyPaid(): bool
    {
        return $this->getRemainingBalance() <= 0;
    }

    public function hasPendingPayments(): bool
    {
        foreach ($this->payments as $payment) {
            if ($payment->getStatus() === 'pending') {
                return true;
            }
        }
        return false;
    }

    public function canCreatePayment(): bool
    {
        return $this->status !== 'cancelled' && $this->getRemainingBalance() > 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        $this->calculateTotal();
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

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;
        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return Collection<int, Payment>
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): static
    {
        if (!$this->payments->contains($payment)) {
            $this->payments->add($payment);
            $payment->setBooking($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): static
    {
        if ($this->payments->removeElement($payment)) {
            if ($payment->getBooking() === $this) {
                $payment->setBooking(null);
            }
        }

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
            $ticket->setBooking($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): static
    {
        if ($this->tickets->removeElement($ticket)) {
            if ($ticket->getBooking() === $this) {
                $ticket->setBooking(null);
            }
        }

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('Réservation #%d - %d billet(s)', $this->id, $this->quantity);
    }
}
