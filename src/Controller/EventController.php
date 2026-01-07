<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Service\ImageUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/events')]
#[IsGranted('ROLE_ADMIN')]
class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator, EventRepository $eventRepository): Response
    {
        $queryBuilder = $eventRepository->createQueryBuilder('e')
            ->orderBy('e.dateEvent', 'ASC');

        $events = $paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            15 // 15 événements par page
        );

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ImageUploadService $imageUploadService): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'image : priorité à l'upload de fichier, sinon URL
            $imageFile = $form->get('imageFile')->getData();
            $imageUrl = $form->get('imageUrl')->getData();
            
            if ($imageFile) {
                // Upload de fichier
                $imageFileName = $imageUploadService->upload($imageFile);
                $event->setImage($imageFileName);
            } elseif ($imageUrl) {
                // Utilisation de l'URL
                $event->setImage($imageUrl);
            } else {
                // Validation : au moins un des deux doit être fourni
                $this->addFlash('error', 'Veuillez soit uploader une image, soit fournir une URL d\'image.');
                return $this->render('event/new.html.twig', [
                    'event' => $event,
                    'form' => $form->createView(),
                ]);
            }

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Événement créé avec succès !');
            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager, ImageUploadService $imageUploadService): Response
    {
        $oldImage = $event->getImage();
        $form = $this->createForm(EventType::class, $event, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'image : priorité à l'upload de fichier, sinon URL, sinon garder l'ancienne
            $imageFile = $form->get('imageFile')->getData();
            $imageUrl = $form->get('imageUrl')->getData();
            
            if ($imageFile) {
                // Upload de nouveau fichier
                if ($oldImage && !str_starts_with($oldImage, 'http') && file_exists($imageUploadService->getTargetDirectory() . '/' . $oldImage)) {
                    $imageUploadService->delete($oldImage);
                }
                $imageFileName = $imageUploadService->upload($imageFile);
                $event->setImage($imageFileName);
            } elseif ($imageUrl) {
                // Utilisation de la nouvelle URL
                if ($oldImage && !str_starts_with($oldImage, 'http') && file_exists($imageUploadService->getTargetDirectory() . '/' . $oldImage)) {
                    $imageUploadService->delete($oldImage);
                }
                $event->setImage($imageUrl);
            } else {
                // Garder l'ancienne image si aucune nouvelle n'est fournie
                $event->setImage($oldImage);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Événement modifié avec succès !');
            return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager, ImageUploadService $imageUploadService): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            // Supprimer l'image associée
            if ($event->getImage()) {
                $imageUploadService->delete($event->getImage());
            }
            
            $entityManager->remove($event);
            $entityManager->flush();

            $this->addFlash('success', 'Événement supprimé avec succès !');
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }
}
