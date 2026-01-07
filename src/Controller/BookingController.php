<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Ticket;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use App\Repository\CategoryRepository;
use App\Repository\EventRepository;
use App\Repository\VenueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/booking')]
class BookingController extends AbstractController
{
    #[Route('/', name: 'app_booking_index', methods: ['GET'])]
    public function index(
        Request $request, 
        PaginatorInterface $paginator,
        EventRepository $eventRepository, 
        CategoryRepository $categoryRepository, 
        VenueRepository $venueRepository
    ): Response {
        $search = $request->query->get('search', '');
        $categoryParam = $request->query->get('category', '');
        $venueParam = $request->query->get('venue', '');
        $dateFromParam = $request->query->get('dateFrom', '');
        $dateToParam = $request->query->get('dateTo', '');
        $priceMinParam = $request->query->get('priceMin', '');
        $priceMaxParam = $request->query->get('priceMax', '');

        $categoryId = !empty($categoryParam) && is_numeric($categoryParam) ? (int)$categoryParam : null;
        $venueId = !empty($venueParam) && is_numeric($venueParam) ? (int)$venueParam : null;
        
        // Parse dates
        $dateFrom = null;
        if ($dateFromParam) {
            try {
                $dateFrom = new \DateTime($dateFromParam);
            } catch (\Exception $e) {
                $dateFrom = null;
            }
        }
        
        $dateTo = null;
        if ($dateToParam) {
            try {
                $dateTo = new \DateTime($dateToParam);
                $dateTo->setTime(23, 59, 59); // Fin de journée
            } catch (\Exception $e) {
                $dateTo = null;
            }
        }

        // Parse prices
        $priceMin = !empty($priceMinParam) && is_numeric($priceMinParam) ? (float)$priceMinParam : null;
        $priceMax = !empty($priceMaxParam) && is_numeric($priceMaxParam) ? (float)$priceMaxParam : null;

        // Créer la requête avec tous les filtres
        $queryBuilder = $eventRepository->createQueryBuilderWithFilters(
            $search ?: null,
            $categoryId,
            $venueId,
            $dateFrom,
            $dateTo,
            $priceMin,
            $priceMax
        );

        // Paginer les résultats
        $events = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            9 // 9 événements par page
        );

        $categories = $categoryRepository->findAll();
        $venues = $venueRepository->findAll();

        return $this->render('booking/events.html.twig', [
            'events' => $events,
            'categories' => $categories,
            'venues' => $venues,
            'currentSearch' => $search,
            'currentCategory' => $categoryId,
            'currentVenue' => $venueId,
            'currentDateFrom' => $dateFromParam,
            'currentDateTo' => $dateToParam,
            'currentPriceMin' => $priceMinParam,
            'currentPriceMax' => $priceMaxParam,
        ]);
    }

    #[Route('/reservations', name: 'app_booking_reservations', methods: ['GET'])]
    public function reservations(Request $request, PaginatorInterface $paginator, BookingRepository $bookingRepository, EventRepository $eventRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());

        // Récupérer les paramètres de filtres
        $status = $request->query->get('status', '');
        $dateFromParam = $request->query->get('dateFrom', '');
        $dateToParam = $request->query->get('dateTo', '');
        $totalMinParam = $request->query->get('totalMin', '');
        $totalMaxParam = $request->query->get('totalMax', '');
        $eventParam = $request->query->get('event', '');

        // Parse dates
        $dateFrom = null;
        if ($dateFromParam) {
            try {
                $dateFrom = new \DateTime($dateFromParam);
            } catch (\Exception $e) {
                $dateFrom = null;
            }
        }
        
        $dateTo = null;
        if ($dateToParam) {
            try {
                $dateTo = new \DateTime($dateToParam);
                $dateTo->setTime(23, 59, 59);
            } catch (\Exception $e) {
                $dateTo = null;
            }
        }

        // Parse totals
        $totalMin = !empty($totalMinParam) && is_numeric($totalMinParam) ? (float)$totalMinParam : null;
        $totalMax = !empty($totalMaxParam) && is_numeric($totalMaxParam) ? (float)$totalMaxParam : null;
        $eventId = !empty($eventParam) && is_numeric($eventParam) ? (int)$eventParam : null;

        // Si admin, utiliser la méthode admin, sinon filtrer par utilisateur
        if ($isAdmin) {
            $queryBuilder = $bookingRepository->createQueryBuilderForAdmin(
                $status ?: null,
                $dateFrom,
                $dateTo,
                $totalMin,
                $totalMax,
                $eventId,
                null // Tous les utilisateurs
            );
        } else {
            // Créer la requête avec filtres pour l'utilisateur
            $queryBuilder = $bookingRepository->createQueryBuilderWithFilters(
                $user,
                $status ?: null,
                $dateFrom,
                $dateTo,
                $totalMin,
                $totalMax,
                $eventId
            );
        }

        // Paginer les résultats
        $bookings = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10 // 10 réservations par page
        );

        // Récupérer les événements pour le filtre
        $events = $eventRepository->findAll();

        return $this->render('booking/index.html.twig', [
            'bookings' => $bookings,
            'events' => $events,
            'currentStatus' => $status,
            'currentDateFrom' => $dateFromParam,
            'currentDateTo' => $dateToParam,
            'currentTotalMin' => $totalMinParam,
            'currentTotalMax' => $totalMaxParam,
            'currentEvent' => $eventId,
            'isAdmin' => $isAdmin,
        ]);
    }

    #[Route('/cart', name: 'app_booking_cart', methods: ['GET'])]
    public function cart(Request $request, PaginatorInterface $paginator, BookingRepository $bookingRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Créer la requête pour les bookings actifs de l'utilisateur
        $queryBuilder = $bookingRepository->createQueryBuilder('b')
            ->where('b.user = :user')
            ->andWhere('b.status != :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'cancelled')
            ->orderBy('b.createdAt', 'DESC');

        // Paginer les résultats
        $bookings = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            10 // 10 réservations par page
        );

        return $this->render('booking/cart.html.twig', [
            'bookings' => $bookings,
        ]);
    }

    #[Route('/calendar', name: 'app_booking_calendar', methods: ['GET'])]
    public function calendar(Request $request, EventRepository $eventRepository): Response
    {
        // Récupérer le mois et l'année depuis les paramètres ou utiliser la date actuelle
        $year = $request->query->getInt('year', (int)date('Y'));
        $month = $request->query->getInt('month', (int)date('n'));
        
        // Validation
        if ($month < 1 || $month > 12) {
            $month = (int)date('n');
        }
        if ($year < 2020 || $year > 2100) {
            $year = (int)date('Y');
        }

        // Créer la date du premier jour du mois
        $firstDay = new \DateTime("$year-$month-01");
        
        // Calculer le premier jour du calendrier (peut être du mois précédent)
        $firstDayOfWeek = (int)$firstDay->format('w'); // 0 = dimanche, 1 = lundi, etc.
        $startDate = clone $firstDay;
        $startDate->modify('-' . (($firstDayOfWeek + 6) % 7) . ' days'); // Commencer le lundi
        
        // Calculer le dernier jour du calendrier
        $lastDay = clone $firstDay;
        $lastDay->modify('last day of this month');
        $lastDayOfWeek = (int)$lastDay->format('w');
        $daysToAdd = 7 - (($lastDayOfWeek + 6) % 7) - 1;
        $endDate = clone $lastDay;
        if ($daysToAdd > 0) {
            $endDate->modify("+$daysToAdd days");
        }
        
        // Récupérer tous les événements du mois
        $events = $eventRepository->createQueryBuilder('e')
            ->where('e.dateEvent >= :startDate')
            ->andWhere('e.dateEvent <= :endDate')
            ->setParameter('startDate', $firstDay->format('Y-m-01'))
            ->setParameter('endDate', $lastDay->format('Y-m-t'))
            ->orderBy('e.dateEvent', 'ASC')
            ->getQuery()
            ->getResult();
        
        // Organiser les événements par jour
        $eventsByDay = [];
        foreach ($events as $event) {
            $dayKey = $event->getDateEvent()->format('Y-m-d');
            if (!isset($eventsByDay[$dayKey])) {
                $eventsByDay[$dayKey] = [];
            }
            $eventsByDay[$dayKey][] = $event;
        }

        // Générer les jours du calendrier
        $calendarDays = [];
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $dayKey = $currentDate->format('Y-m-d');
            $calendarDays[] = [
                'date' => clone $currentDate,
                'events' => $eventsByDay[$dayKey] ?? [],
                'isCurrentMonth' => $currentDate->format('Y-m') === "$year-$month",
                'isToday' => $currentDate->format('Y-m-d') === date('Y-m-d'),
            ];
            $currentDate->modify('+1 day');
        }

        // Mois précédent et suivant
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }
        
        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }

        // Noms des mois en français
        $monthNames = [
            1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
            5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
            9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
        ];

        return $this->render('booking/calendar.html.twig', [
            'calendarDays' => $calendarDays,
            'currentMonth' => $month,
            'currentYear' => $year,
            'monthName' => $monthNames[$month],
            'prevMonth' => $prevMonth,
            'prevYear' => $prevYear,
            'nextMonth' => $nextMonth,
            'nextYear' => $nextYear,
            'today' => new \DateTime(),
        ]);
    }

    #[Route('/events', name: 'app_booking_events', methods: ['GET'])]
    public function events(
        Request $request, 
        PaginatorInterface $paginator,
        EventRepository $eventRepository, 
        CategoryRepository $categoryRepository, 
        VenueRepository $venueRepository
    ): Response {
        $search = $request->query->get('search', '');
        $categoryParam = $request->query->get('category', '');
        $venueParam = $request->query->get('venue', '');
        $dateFromParam = $request->query->get('dateFrom', '');
        $dateToParam = $request->query->get('dateTo', '');
        $priceMinParam = $request->query->get('priceMin', '');
        $priceMaxParam = $request->query->get('priceMax', '');

        $categoryId = !empty($categoryParam) && is_numeric($categoryParam) ? (int)$categoryParam : null;
        $venueId = !empty($venueParam) && is_numeric($venueParam) ? (int)$venueParam : null;
        
        // Parse dates
        $dateFrom = null;
        if ($dateFromParam) {
            try {
                $dateFrom = new \DateTime($dateFromParam);
            } catch (\Exception $e) {
                $dateFrom = null;
            }
        }
        
        $dateTo = null;
        if ($dateToParam) {
            try {
                $dateTo = new \DateTime($dateToParam);
                $dateTo->setTime(23, 59, 59);
            } catch (\Exception $e) {
                $dateTo = null;
            }
        }

        // Parse prices
        $priceMin = !empty($priceMinParam) && is_numeric($priceMinParam) ? (float)$priceMinParam : null;
        $priceMax = !empty($priceMaxParam) && is_numeric($priceMaxParam) ? (float)$priceMaxParam : null;

        // Créer la requête avec tous les filtres
        $queryBuilder = $eventRepository->createQueryBuilderWithFilters(
            $search ?: null,
            $categoryId,
            $venueId,
            $dateFrom,
            $dateTo,
            $priceMin,
            $priceMax
        );

        // Paginer les résultats
        $events = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            9 // 9 événements par page
        );

        $categories = $categoryRepository->findAll();
        $venues = $venueRepository->findAll();

        return $this->render('booking/events.html.twig', [
            'events' => $events,
            'categories' => $categories,
            'venues' => $venues,
            'currentSearch' => $search,
            'currentCategory' => $categoryId,
            'currentVenue' => $venueId,
            'currentDateFrom' => $dateFromParam,
            'currentDateTo' => $dateToParam,
            'currentPriceMin' => $priceMinParam,
            'currentPriceMax' => $priceMaxParam,
        ]);
    }

    #[Route('/events/{id}', name: 'app_booking_event_show', methods: ['GET'])]
    public function eventShow(int $id, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Événement introuvable.');
        }

        return $this->render('booking/event_show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/new', name: 'app_booking_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, EventRepository $eventRepository): Response
    {
        $eventId = $request->query->getInt('id');
        $event = $eventId ? $eventRepository->find($eventId) : null;

        if (!$event) {
            $this->addFlash('warning', 'Veuillez sélectionner un événement à réserver.');
            return $this->redirectToRoute('app_booking_events');
        }

        $booking = new Booking();
        $booking->setEvent($event);
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if (!$user) {
                $this->addFlash('error', 'Vous devez être connecté pour effectuer une réservation.');
                return $this->redirectToRoute('app_login');
            }

            if (!$event->checkAvailability($booking->getQuantity() ?? 0)) {
                $this->addFlash('error', 'Plus de places disponibles pour cette quantité pour cet événement.');
                return $this->redirectToRoute('app_booking_events');
            }

            // Associer la réservation à l'utilisateur connecté
            $booking->setUser($user);
            $booking->createBooking();
            $booking->calculateTotal();

            // Générer les tickets (1 par place) liés au user courant
            for ($i = 0; $i < ($booking->getQuantity() ?? 0); $i++) {
                $ticket = new Ticket();
                $ticket->setSeat('S-' . ($i + 1));
                $ticket->setUser($user);
                $ticket->setBooking($booking);
                $ticket->generateTicket();
                $booking->addTicket($ticket);
            }

            $entityManager->persist($booking);
            $entityManager->flush();

            // Envoyer un email de confirmation
            try {
                $emailService = $this->container->get(\App\Service\EmailNotificationService::class);
                $emailService->sendBookingConfirmation($booking);
            } catch (\Exception $e) {
                // Ne pas bloquer l'utilisateur si l'email échoue
                error_log('Erreur envoi email: ' . $e->getMessage());
            }

            $this->addFlash('success', 'Réservation créée avec succès !');
            return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('booking/new.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/{id}', name: 'app_booking_show', methods: ['GET'])]
    #[IsGranted('VIEW', subject: 'booking')]
    public function show(Booking $booking): Response
    {
        return $this->render('booking/show.html.twig', [
            'booking' => $booking,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_booking_edit', methods: ['GET', 'POST'])]
    #[IsGranted('EDIT', subject: 'booking')]
    public function edit(Request $request, Booking $booking, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $booking->calculateTotal();
            $entityManager->flush();

            $this->addFlash('success', 'Réservation modifiée avec succès !');
            return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('booking/edit.html.twig', [
            'booking' => $booking,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_booking_cancel', methods: ['POST'])]
    #[IsGranted('CANCEL', subject: 'booking')]
    public function cancel(Request $request, Booking $booking, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('cancel'.$booking->getId(), $request->request->get('_token'))) {
            $booking->cancelBooking();
            $entityManager->flush();

            // Envoyer un email d'annulation
            try {
                $emailService = $this->container->get(\App\Service\EmailNotificationService::class);
                $emailService->sendBookingCancellation($booking);
            } catch (\Exception $e) {
                error_log('Erreur envoi email: ' . $e->getMessage());
            }

            $this->addFlash('success', 'Réservation annulée avec succès !');
        }

        return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/delete', name: 'app_booking_delete', methods: ['POST'])]
    #[IsGranted('DELETE', subject: 'booking')]
    public function delete(Request $request, Booking $booking, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$booking->getId(), $request->request->get('_token'))) {
            $entityManager->remove($booking);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation supprimée avec succès !');
        }

        return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
    }
}
