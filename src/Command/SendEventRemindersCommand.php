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
    name: 'app:send-event-reminders',
    description: 'Envoie les rappels d\'événements 24h avant',
)]
class SendEventRemindersCommand extends Command
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

        // Récupérer toutes les réservations confirmées
        $bookings = $this->bookingRepository->createQueryBuilder('b')
            ->where('b.status = :status')
            ->setParameter('status', 'confirmed')
            ->getQuery()
            ->getResult();

        $sent = 0;
        $failed = 0;

        foreach ($bookings as $booking) {
            try {
                if ($this->emailService->sendEventReminder($booking)) {
                    $sent++;
                    $io->info(sprintf('Rappel envoyé pour la réservation #%d', $booking->getId()));
                }
            } catch (\Exception $e) {
                $failed++;
                $io->error(sprintf('Erreur pour la réservation #%d: %s', $booking->getId(), $e->getMessage()));
            }
        }

        $io->success(sprintf('Rappels envoyés : %d | Erreurs : %d', $sent, $failed));

        return Command::SUCCESS;
    }
}

