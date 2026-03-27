<?php

namespace App\Controller;

use App\Components\EventFormComponent;
use App\Entity\Event;
use App\Form\EventSearchType;
use App\Form\EventType;
use App\Form\Model\EventSearch;
use App\Repository\EventRepository;
use App\Service\UpdateEventStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    #[Route('/', name: 'main_event')]
    public function mainEvent(EventRepository $eventRepository, Request $request, UpdateEventStatus $eventStatus): Response
    {
        $eventStatus ->updatePastEvent();

        $eventSearch = new EventSearch();
        $eventFormSearch = $this->createForm(EventSearchType::class, $eventSearch);
        $eventFormSearch->handleRequest($request);

        $events = $eventRepository->findEventList($eventSearch, $this->getUser());

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'eventFormSearch' => $eventFormSearch->createView(),
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
    #[Route('/update_event/{id}', name: 'update_event', requirements: ['id' => '\d+'])]
    public function createEvent(Request $request, EventRepository $eventRepository): Response
    {


        $id = $request->attributes->get('id');
        $event = null;
        if ($id !== null) {
            $event = $eventRepository->find($id);
            if (!$event) {
                throw $this->createNotFoundException('Événement introuvable.');
            }
        if ($id !== null && $event->getOrganizer() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cet événement.');
        }

        }

        return $this->render('event/createEvent.html.twig', [
            'event' => $event,
        ]);
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


    #[Route('/delete/{id}', name: 'delete_event', requirements: ['id' => '\d+'])]
    public function deleteEvent
    ()
    {

    }
}
