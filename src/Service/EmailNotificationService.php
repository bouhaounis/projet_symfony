<?php

namespace App\Service;

use App\Entity\Booking;
use App\Entity\Payment;
use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailNotificationService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig
    ) {
    }

    private function getFromEmail(): string
    {
        return $_ENV['MAILER_FROM_EMAIL'] ?? 'noreply@example.com';
    }

    private function getFromName(): string
    {
        return $_ENV['MAILER_FROM_NAME'] ?? 'Event Booking System';
    }

    /**
     * Envoie un email de confirmation de réservation
     */
    public function sendBookingConfirmation(Booking $booking): bool
    {
        try {
            $user = $booking->getUser();
            $event = $booking->getEvent();

            if (!$user || !$user->getEmail()) {
                return false;
            }

            $htmlContent = $this->twig->render('emails/booking_confirmation.html.twig', [
                'booking' => $booking,
                'user' => $user,
                'event' => $event,
            ]);

            $email = (new Email())
                ->from(sprintf('%s <%s>', $this->getFromName(), $this->getFromEmail()))
                ->to($user->getEmail())
                ->subject(sprintf('Confirmation de réservation #%d - %s', $booking->getId(), $event?->getNom() ?? 'Événement'))
                ->html($htmlContent);

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            // Log l'erreur si nécessaire
            error_log('Erreur envoi email confirmation réservation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie un email de confirmation de paiement
     */
    public function sendPaymentConfirmation(Payment $payment): bool
    {
        try {
            $booking = $payment->getBooking();
            $user = $booking?->getUser();
            $event = $booking?->getEvent();

            if (!$user || !$user->getEmail()) {
                return false;
            }

            $htmlContent = $this->twig->render('emails/payment_confirmation.html.twig', [
                'payment' => $payment,
                'booking' => $booking,
                'user' => $user,
                'event' => $event,
            ]);

            $email = (new Email())
                ->from(sprintf('%s <%s>', $this->getFromName(), $this->getFromEmail()))
                ->to($user->getEmail())
                ->subject(sprintf('Confirmation de paiement #%d - %s€', $payment->getId(), number_format($payment->getAmount(), 2, ',', ' ')))
                ->html($htmlContent);

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            error_log('Erreur envoi email confirmation paiement: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie un email de rappel d'événement (24h avant)
     */
    public function sendEventReminder(Booking $booking): bool
    {
        try {
            $user = $booking->getUser();
            $event = $booking->getEvent();

            if (!$user || !$user->getEmail() || !$event) {
                return false;
            }

            // Vérifier que l'événement est dans 24h environ (entre 23h et 25h avant)
            $now = new \DateTime();
            $eventDate = clone $event->getDateEvent();
            
            // Calculer la différence en heures
            $hoursDiff = ($eventDate->getTimestamp() - $now->getTimestamp()) / 3600;

            // Ne pas envoyer si l'événement est dans plus de 25h ou moins de 23h
            if ($hoursDiff > 25 || $hoursDiff < 23) {
                return false;
            }

            $htmlContent = $this->twig->render('emails/event_reminder.html.twig', [
                'booking' => $booking,
                'user' => $user,
                'event' => $event,
            ]);

            $email = (new Email())
                ->from(sprintf('%s <%s>', $this->getFromName(), $this->getFromEmail()))
                ->to($user->getEmail())
                ->subject(sprintf('Rappel : %s demain !', $event->getNom()))
                ->html($htmlContent);

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            error_log('Erreur envoi email rappel événement: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie un email d'annulation de réservation
     */
    public function sendBookingCancellation(Booking $booking, ?string $reason = null): bool
    {
        try {
            $user = $booking->getUser();
            $event = $booking->getEvent();

            if (!$user || !$user->getEmail()) {
                return false;
            }

            $htmlContent = $this->twig->render('emails/booking_cancellation.html.twig', [
                'booking' => $booking,
                'user' => $user,
                'event' => $event,
                'reason' => $reason,
            ]);

            $email = (new Email())
                ->from(sprintf('%s <%s>', $this->getFromName(), $this->getFromEmail()))
                ->to($user->getEmail())
                ->subject(sprintf('Annulation de réservation #%d', $booking->getId()))
                ->html($htmlContent);

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            error_log('Erreur envoi email annulation réservation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie un email de rappel de paiement (si réservation non payée)
     */
    public function sendPaymentReminder(Booking $booking): bool
    {
        try {
            $user = $booking->getUser();
            $event = $booking->getEvent();

            if (!$user || !$user->getEmail() || !$event) {
                return false;
            }

            // Ne pas envoyer si déjà payé en totalité
            if ($booking->getRemainingBalance() <= 0) {
                return false;
            }

            $htmlContent = $this->twig->render('emails/payment_reminder.html.twig', [
                'booking' => $booking,
                'user' => $user,
                'event' => $event,
            ]);

            $email = (new Email())
                ->from(sprintf('%s <%s>', $this->getFromName(), $this->getFromEmail()))
                ->to($user->getEmail())
                ->subject(sprintf('Rappel de paiement - Réservation #%d', $booking->getId()))
                ->html($htmlContent);

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            error_log('Erreur envoi email rappel paiement: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envoie un email de bienvenue à un nouvel utilisateur
     */
    public function sendWelcomeEmail(User $user): bool
    {
        try {
            if (!$user->getEmail()) {
                return false;
            }

            $htmlContent = $this->twig->render('emails/welcome.html.twig', [
                'user' => $user,
            ]);

            $email = (new Email())
                ->from(sprintf('%s <%s>', $this->getFromName(), $this->getFromEmail()))
                ->to($user->getEmail())
                ->subject('Bienvenue sur notre plateforme !')
                ->html($htmlContent);

            $this->mailer->send($email);
            return true;
        } catch (\Exception $e) {
            error_log('Erreur envoi email bienvenue: ' . $e->getMessage());
            return false;
        }
    }
}

