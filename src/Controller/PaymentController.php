<?php

namespace App\Controller;

use App\Entity\Payment;
use App\Form\PaymentType;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route('/', name: 'app_payment_index', methods: ['GET'])]
    public function index(PaymentRepository $paymentRepository): Response
    {
        return $this->render('payment/index.html.twig', [
            'payments' => $paymentRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_payment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $payment = new Payment();
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($payment);
            $entityManager->flush();

            $this->addFlash('success', 'Paiement créé avec succès !');
            if ($payment->getBooking()) {
                return $this->redirectToRoute('app_booking_show', ['id' => $payment->getBooking()->getId()], Response::HTTP_SEE_OTHER);
            }
            return $this->redirectToRoute('app_payment_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('payment/new.html.twig', [
            'payment' => $payment,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/new/for-booking/{bookingId}', name: 'app_payment_new_for_booking', methods: ['GET', 'POST'])]
    public function newForBooking(Request $request, EntityManagerInterface $entityManager, int $bookingId): Response
    {
        $booking = $entityManager->getRepository(\App\Entity\Booking::class)->find($bookingId);

        if (!$booking) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        // Vérifier si la réservation est déjà payée
        if (!$booking->canCreatePayment()) {
            $this->addFlash('warning', 'Cette réservation est déjà payée en totalité.');
            return $this->redirectToRoute('app_booking_cart', [], Response::HTTP_SEE_OTHER);
        }

        $payment = new Payment();
        $payment->setBooking($booking);
        $payment->setAmount($booking->getRemainingBalance());

        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier à nouveau avant de créer le paiement
            if (!$booking->canCreatePayment()) {
                $this->addFlash('warning', 'Cette réservation est déjà payée en totalité.');
                return $this->redirectToRoute('app_booking_cart', [], Response::HTTP_SEE_OTHER);
            }

            // S'assurer que le montant ne dépasse pas le solde restant
            $remainingBalance = $booking->getRemainingBalance();
            if ($payment->getAmount() > $remainingBalance) {
                $payment->setAmount($remainingBalance);
            }

            $entityManager->persist($payment);
            $entityManager->flush();

            $this->addFlash('success', 'Paiement créé avec succès ! Vous pouvez créer un nouveau paiement si nécessaire.');
            
            // Recharger la réservation pour avoir les données à jour
            $entityManager->refresh($booking);
            
            // Si il reste un solde, rediriger vers le panier pour permettre de repayer
            if ($booking->canCreatePayment()) {
                return $this->redirectToRoute('app_booking_cart', [], Response::HTTP_SEE_OTHER);
            }
            
            // Sinon rediriger vers les détails de la réservation
            return $this->redirectToRoute('app_booking_show', ['id' => $bookingId], Response::HTTP_SEE_OTHER);
        }

        return $this->render('payment/new_for_booking.html.twig', [
            'payment' => $payment,
            'booking' => $booking,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_payment_show', methods: ['GET'])]
    public function show(Payment $payment): Response
    {
        return $this->render('payment/show.html.twig', [
            'payment' => $payment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_payment_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Payment $payment, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PaymentType::class, $payment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Paiement modifié avec succès !');
            return $this->redirectToRoute('app_payment_show', ['id' => $payment->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('payment/edit.html.twig', [
            'payment' => $payment,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/process', name: 'payment_process', methods: ['POST'])]
    public function processPayment(Payment $payment, EntityManagerInterface $entityManager): Response
    {
        if (!$payment->isProcessable()) {
            $this->addFlash('error', 'Ce paiement ne peut pas être traité');
            return $this->redirectToRoute('app_payment_show', ['id' => $payment->getId()], Response::HTTP_SEE_OTHER);
        }

        $success = $payment->processPayment();

        if ($success) {
            $entityManager->flush();
            $this->addFlash('success', '✅ Paiement traité avec succès !');
        } else {
            $this->addFlash('error', '❌ Échec du traitement du paiement');
        }

        return $this->redirectToRoute('app_payment_show', ['id' => $payment->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/refund', name: 'payment_refund', methods: ['POST'])]
    public function refundPayment(Payment $payment, EntityManagerInterface $entityManager): Response
    {
        if (!$payment->isRefundable()) {
            $this->addFlash('error', 'Ce paiement ne peut pas être remboursé');
            return $this->redirectToRoute('app_payment_show', ['id' => $payment->getId()], Response::HTTP_SEE_OTHER);
        }

        $success = $payment->refund();

        if ($success) {
            $entityManager->flush();
            $this->addFlash('success', '✅ Remboursement effectué avec succès !');
        } else {
            $this->addFlash('error', '❌ Échec du remboursement');
        }

        return $this->redirectToRoute('app_payment_show', ['id' => $payment->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_payment_delete', methods: ['POST'])]
    public function delete(Request $request, Payment $payment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$payment->getId(), $request->request->get('_token'))) {
            $entityManager->remove($payment);
            $entityManager->flush();

            $this->addFlash('success', 'Paiement supprimé avec succès');
        }

        return $this->redirectToRoute('app_payment_index', [], Response::HTTP_SEE_OTHER);
    }
}
