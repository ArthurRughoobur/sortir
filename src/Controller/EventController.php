<?php

namespace App\Controller;

use App\Components\EventFormComponent;
use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
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
        $events = $eventRepository->findAll();
        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/detail/{id}', name: 'event_detail', requirements: ['id' => '\d+'])]
    public function detailEvent(int $id, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($id);

        return $this->render('event/detailEvent.html.twig', [
            'event' => $event,
        ]);
    }


    #[Route('/create_event', name: 'create_event', methods: ['GET', 'POST'])]
    public function createEvent(EventFormComponent $component): Response
    {
//        $component -> save();
//        $component -> publish();

//        $event = new Event();
//        $eventForm = $this->createForm(EventType::class, $event);
//
//        $eventForm->handleRequest($request);
//        if ($eventForm->isSubmitted() && $eventForm->isValid()) {
//            if ($eventForm->getClickedButton() && 'save' === $eventForm->getClickedButton()->getName()) {
//                $event->setStatus("En création");
//                $this->addFlash('success', ['Evènement '. $event->getName().'sauvegardé !']);
//            }
//            if ($eventForm->getClickedButton() && 'publish' === $eventForm->getClickedButton()->getName()) {
//                $event->setStatus("Ouverte");
//                $this->addFlash('success', ['Evènement '. $event->getName().'crée !']);
//            }
//            if ($eventForm->getClickedButton() && 'cancel' === $eventForm->getClickedButton()->getName()) {
//                $this->addFlash('success', ['Evènement '. $event->getName().'annulé !']);
//                return $this->redirectToRoute('main_event');
//
//            }
//            $entityManager->persist($event);
//            $entityManager->flush();

//            return $this->redirectToRoute('main_event');
//        }
        return $this->render('event/createEvent.html.twig', [

        ]);

    }
}

