<?php

namespace App\Command;

use App\Repository\BookingRepository;
use App\Service\EmailNotificationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:send-payment-reminders',
    description: 'Envoie les rappels de paiement pour les réservations non payées',
)]
class SendPaymentRemindersCommand extends Command
{
    public function __construct(
        private BookingRepository $bookingRepository,
        private EmailNotificationService $emailService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Récupérer toutes les réservations avec un solde restant
        $bookings = $this->bookingRepository->createQueryBuilder('b')
            ->where('b.status = :status')
            ->setParameter('status', 'confirmed')
            ->getQuery()
            ->getResult();

        $sent = 0;
        $failed = 0;

        foreach ($bookings as $booking) {
            // Vérifier si il y a un solde à payer
            if ($booking->getRemainingBalance() > 0) {
                try {
                    if ($this->emailService->sendPaymentReminder($booking)) {
                        $sent++;
                        $io->info(sprintf('Rappel de paiement envoyé pour la réservation #%d', $booking->getId()));
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $io->error(sprintf('Erreur pour la réservation #%d: %s', $booking->getId(), $e->getMessage()));
                }
            }
        }

        $io->success(sprintf('Rappels de paiement envoyés : %d | Erreurs : %d', $sent, $failed));

        return Command::SUCCESS;
    }
}


