<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/', name: 'main_event')]
    public function mainEvent(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findEventList();
        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/detail/{id}', name: 'event_detail', requirements: ['id' => '\d+'])]
    public function detailEvent(int $id, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->findEventById($id);

        return $this->render('event/detailEvent.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/create_event', name: 'create_event')]
    public function createEvent(EventRepository $eventRepository): Response
    {
    }

    #[Route('/inscription/{id}', name: 'inscription_event', requirements: ['id' => '\d+'])]
    public function inscriptionEvent
    (
        int                    $id,
        EventRepository        $eventRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $event = $eventRepository->find($id);
        $user = $this->getUser();
        if (!$event) {
            throw $this->createNotFoundException('Événement introuvable.');
        }
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour vous inscrire.');
        }
        $event->addRegistred($user);
        $entityManager->persist($event);
        $entityManager->flush();
        $this->addFlash('success', 'Vous été bien inscrit.');
        return $this->redirectToRoute('event_detail', ['id' => $id]);

    }


    #[Route('/desinscription/{id}', name: 'desinscription_event', requirements: ['id' => '\d+'])]
    public function desinscriptionEvent
    (
        int                    $id,
        EventRepository        $eventRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {
$event = $eventRepository->findEventById($id);
$user = $this->getUser();
        if (!$event) {
            throw $this->createNotFoundException('Événement introuvable.');
        }
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour vous inscrire.');
        }
        $event->removeRegistred($user);
        $entityManager->persist($event);
        $entityManager->flush();
        $this->addFlash('success', 'Vous été bien désinscrit.');
return $this->redirectToRoute('event_detail', ['id' => $id]);
    }

}
