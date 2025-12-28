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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/booking')]
class BookingController extends AbstractController
{
    #[Route('/', name: 'app_booking_index', methods: ['GET'])]
    public function index(Request $request, EventRepository $eventRepository, CategoryRepository $categoryRepository, VenueRepository $venueRepository): Response
    {
        $search = $request->query->get('search', '');
        $categoryParam = $request->query->get('category', '');
        $venueParam = $request->query->get('venue', '');
        $categoryId = !empty($categoryParam) && is_numeric($categoryParam) ? (int)$categoryParam : null;
        $venueId = !empty($venueParam) && is_numeric($venueParam) ? (int)$venueParam : null;

        $events = $eventRepository->searchAndFilter($search ?: null, $categoryId, $venueId);
        $categories = $categoryRepository->findAll();
        $venues = $venueRepository->findAll();

        return $this->render('booking/events.html.twig', [
            'events' => $events,
            'categories' => $categories,
            'venues' => $venues,
            'currentSearch' => $search,
            'currentCategory' => $categoryId,
            'currentVenue' => $venueId,
        ]);
    }

    #[Route('/reservations', name: 'app_booking_reservations', methods: ['GET'])]
    public function reservations(BookingRepository $bookingRepository): Response
    {
        return $this->render('booking/index.html.twig', [
            'bookings' => $bookingRepository->findAll(),
        ]);
    }

    #[Route('/cart', name: 'app_booking_cart', methods: ['GET'])]
    public function cart(BookingRepository $bookingRepository): Response
    {
        $allBookings = $bookingRepository->findAll();
        // Afficher toutes les réservations non annulées pour voir leur statut
        $activeBookings = array_filter($allBookings, function($booking) {
            return $booking->getStatus() !== 'cancelled';
        });

        return $this->render('booking/cart.html.twig', [
            'bookings' => $activeBookings,
        ]);
    }

    #[Route('/events', name: 'app_booking_events', methods: ['GET'])]
    public function events(Request $request, EventRepository $eventRepository, CategoryRepository $categoryRepository, VenueRepository $venueRepository): Response
    {
        $search = $request->query->get('search', '');
        $categoryParam = $request->query->get('category', '');
        $venueParam = $request->query->get('venue', '');
        $categoryId = !empty($categoryParam) && is_numeric($categoryParam) ? (int)$categoryParam : null;
        $venueId = !empty($venueParam) && is_numeric($venueParam) ? (int)$venueParam : null;

        $events = $eventRepository->searchAndFilter($search ?: null, $categoryId, $venueId);
        $categories = $categoryRepository->findAll();
        $venues = $venueRepository->findAll();

        return $this->render('booking/events.html.twig', [
            'events' => $events,
            'categories' => $categories,
            'venues' => $venues,
            'currentSearch' => $search,
            'currentCategory' => $categoryId,
            'currentVenue' => $venueId,
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
            if (!$event->checkAvailability($booking->getQuantity() ?? 0)) {
                $this->addFlash('error', 'Plus de places disponibles pour cette quantité pour cet événement.');
                return $this->redirectToRoute('app_booking_events');
            }

            $booking->createBooking();
            $booking->calculateTotal();

            // Générer les tickets (1 par place) liés au user courant
            $user = $this->getUser();
            if ($user) {
                for ($i = 0; $i < ($booking->getQuantity() ?? 0); $i++) {
                    $ticket = new Ticket();
                    $ticket->setSeat('S-' . ($i + 1));
                    $ticket->setUser($user);
                    $ticket->setBooking($booking);
                    $ticket->generateTicket();
                    $booking->addTicket($ticket);
                }
            }

            $entityManager->persist($booking);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation créée avec succès !');
            return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('booking/new.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/{id}', name: 'app_booking_show', methods: ['GET'])]
    public function show(Booking $booking): Response
    {
        return $this->render('booking/show.html.twig', [
            'booking' => $booking,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_booking_edit', methods: ['GET', 'POST'])]
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
    public function cancel(Request $request, Booking $booking, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('cancel'.$booking->getId(), $request->request->get('_token'))) {
            $booking->cancelBooking();
            $entityManager->flush();

            $this->addFlash('success', 'Réservation annulée avec succès !');
        }

        return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_booking_delete', methods: ['POST'])]
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
