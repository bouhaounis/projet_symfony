<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Service\TicketPdfGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/ticket')]
class TicketController extends AbstractController
{
    #[Route('/{id}/pdf', name: 'app_ticket_pdf', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function pdf(Ticket $ticket, TicketPdfGenerator $pdfGenerator): Response
    {
        $user = $this->getUser();
        if (!$user || ($ticket->getUser() !== $user && !$this->isGranted('ROLE_ADMIN'))) {
            throw $this->createAccessDeniedException();
        }

        $pdf = $pdfGenerator->generate($ticket);

        return new Response(
            $pdf,
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="ticket-'.$ticket->getId().'.pdf"',
            ]
        );
    }
}




