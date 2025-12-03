<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/booking')]
class BookingController extends AbstractController
{
    #[Route('/', name: 'app_booking_index', methods: ['GET'])]
    public function index(BookingRepository $bookingRepository): Response
    {
        return $this->render('booking/index.html.twig', [
            'bookings' => $bookingRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_booking_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $booking = new Booking();
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $booking->createBooking();
            $booking->calculateTotal();

            $entityManager->persist($booking);
            $entityManager->flush();

            $this->addFlash('success', 'Booking created successfully!');
            return $this->redirectToRoute('app_booking_index');
        }

        return $this->render('booking/new.html.twig', [
            'form' => $form->createView(),
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

            $this->addFlash('success', 'Booking updated successfully!');
            return $this->redirectToRoute('app_booking_index');
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

            $this->addFlash('success', 'Booking cancelled successfully!');
        }

        return $this->redirectToRoute('app_booking_index');
    }

    #[Route('/{id}', name: 'app_booking_delete', methods: ['POST'])]
    public function delete(Request $request, Booking $booking, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$booking->getId(), $request->request->get('_token'))) {
            $entityManager->remove($booking);
            $entityManager->flush();

            $this->addFlash('success', 'Booking deleted successfully!');
        }

        return $this->redirectToRoute('app_booking_index');
    }
}
