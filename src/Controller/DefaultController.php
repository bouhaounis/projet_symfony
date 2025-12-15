<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\EventRepository;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_booking_index');
    }

    #[Route('/_fragments/featured-events', name: 'app_featured_events', methods: ['GET'])]
    public function featuredEvents(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findBy([], ['dateEvent' => 'ASC'], 6);

        return $this->render('partials/_featured_events.html.twig', [
            'events' => $events,
        ]);
    }
}
