<?php

namespace App\Controller;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/', name: 'main_event')]
    public function mainEvent(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findEventList();
        return $this->render('event/index.html.twig',[
            'events' => $events,
        ]);
    }
    #[Route('/detail/{id}', name: 'event_detail', requirements: ['id' => '\d+'])]
    public function detailEvent(int $id, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->findEventById($id);

        return $this->render('event/detailEvent.html.twig',[
            'event' => $event,
        ]);
    }



    #[Route('/create_event', name: 'create_event')]
    public function createEvent(EventRepository $eventRepository): Response
    {
    }
    }

