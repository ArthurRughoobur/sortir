<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EventSearchType;
use App\Form\Model\EventSearch;
use App\Repository\EventRepository;
use App\Security\Voter\EventVoter;
use App\Service\UpdateEventStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EventController extends AbstractController
{
    /**
     * Affiche la page principale des événements avec un système de recherche et filtrage.
     *
     * Cette méthode :
     * - Vérifie que l'utilisateur est authentifié (ROLE_USER requis)
     * - Met à jour automatiquement :
     *      - les événements passés (clôture automatique)
     *      - les événements atteignant leur capacité maximale
     * - Initialise et traite le formulaire de recherche d'événements
     * - Récupère la liste des événements filtrés selon les critères de recherche et l'utilisateur
     * - Filtre les événements appartenant au même campus que l'utilisateur
     * - Retourne la vue avec les données nécessaires
     *
     * @param EventRepository $eventRepository Repository permettant de récupérer les événements
     * @param Request $request Requête HTTP contenant les données du formulaire
     * @param UpdateEventStatus $eventStatus Service de mise à jour des événements passés
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * Si l'utilisateur n'est pas authentifié
     *
     * @return Response Réponse HTTP contenant la page des événements
     */
    #[Route('/', name: 'main_event')]
    public function mainEvent(
        EventRepository $eventRepository,
        Request $request,
        UpdateEventStatus $eventStatus,
    ): Response
    {
        // Vérifie que l'utilisateur possède le rôle requis
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Met à jour les événements passés (ex : clôture automatique)
        $eventStatus->updatePastEvent();

        // Met à jour les événements en fonction de leur capacité maximale
        $eventStatus->syncEventStatusesWithCapacity();

        // Initialisation de l'objet de recherche et du formulaire associé
        $eventSearch = new EventSearch();
        $eventFormSearch = $this->createForm(EventSearchType::class, $eventSearch);
        $eventFormSearch->handleRequest($request);

        // Récupération de l'utilisateur connecté
        $user = $this->getUser();

        // Sécurité supplémentaire (au cas où)
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        // Récupère les événements selon les filtres et l'utilisateur
        $events = $eventRepository->findEventList($eventSearch, $user);

        // Filtre les événements du même campus que l'utilisateur
        $campusEvents = array_values(array_filter($events, function ($event) use ($user) {
            return $event->getCampus() === $user->getCampus();
        }));

        // Retourne la vue avec les données nécessaires
        return $this->render('event/index.html.twig', [
            'events' => $events,
            'campusEvents' => $campusEvents,
            'eventFormSearch' => $eventFormSearch->createView(),
        ]);
    }

    /**
     * Affiche le détail d'un événement.
     *
     * Cette méthode :
     * - Met à jour les statuts des événements en fonction de leur capacité maximale
     * - Récupère un événement via son identifiant
     * - Retourne la vue contenant les informations de l'événement
     *
     * @param int $id Identifiant de l'événement
     * @param EventRepository $eventRepository Repository pour accéder aux événements
     * @param UpdateEventStatus $eventStatusMaxInscription Service de mise à jour des statuts selon la capacité
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * Si l'événement n'existe pas
     *
     * @return Response Réponse HTTP contenant la page de détail de l'événement
     */
    #[Route('/detail/{id}', name: 'event_detail', requirements: ['id' => '\d+'])]
    public function detailEvent(
        int $id,
        EventRepository $eventRepository,
        UpdateEventStatus $eventStatusMaxInscription
    ): Response
    {
        // Mise à jour des statuts des événements selon leur capacité
        $eventStatusMaxInscription->syncEventStatusesWithCapacity();

        // Récupération de l'événement
        $event = $eventRepository->findEventById($id);

        // Vérifie que l'événement existe
        if (!$event) {
            throw $this->createNotFoundException('Événement introuvable.');
        }

        // Retourne la vue avec les données de l'événement
        return $this->render('event/detailEvent.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/create_event', name: 'create_event')]
    #[Route('/update_event/{id}', name: 'update_event', requirements: ['id' => '\d+'])]
    public function createEvent(Request $request, EventRepository $eventRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $id = $request->attributes->get('id');
        $event = null;
        $user = $this->getUser();
        if ($id !== null) {
            $event = $eventRepository->find($id);
            if (!$event) {
                throw $this->createNotFoundException('Événement introuvable.');
            }
            if (!$user instanceof User) {
                throw $this->createAccessDeniedException('Vous devez être connecté pour vous inscrire.');
            }
//            if ($id !== null && $event->getOrganizer() !== $this->getUser()) {
//                throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cet événement.');
//            }
//            $this->denyAccessUnlessGranted(EventVoter::EDIT, $event);

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

    /**
     * Permet à un utilisateur de s'inscrire à un événement.
     *
     * Cette méthode :
     * - Récupère l'événement via son identifiant
     * - Vérifie que l'événement existe
     * - Vérifie que l'utilisateur est authentifié
     * - Vérifie que l'utilisateur n'est pas déjà inscrit à l'événement
     * - Ajoute l'utilisateur à la liste des inscrits
     * - Enregistre les modifications en base de données
     * - Ajoute un message de confirmation
     * - Redirige vers la page de détail de l'événement
     *
     * @param int $id Identifiant de l'événement
     * @param EventRepository $eventRepository Repository pour accéder aux événements
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités pour la persistance
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * Si l'événement n'existe pas
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * Si l'utilisateur n'est pas connecté ou déjà inscrit
     *
     * @return Response Redirection vers la page détail de l'événement
     */
    #[Route('/inscription/{id}', name: 'inscription_event', requirements: ['id' => '\d+'])]
    public function inscriptionEvent(
        int $id,
        EventRepository $eventRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {
        // Récupération de l'événement
        $event = $eventRepository->findEventById($id);

        // Récupération de l'utilisateur connecté
        $user = $this->getUser();

        // Vérifie que l'événement existe
        if (!$event) {
            throw $this->createNotFoundException('Événement introuvable.');
        }

        // Vérifie que l'utilisateur est connecté
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Vérifie que l'utilisateur n'est pas déjà inscrit
        if ($event->getRegistred()->contains($user)) {
            throw $this->createAccessDeniedException('Vous êtes déjà inscrit à cet événement.');
        }

        // Ajoute l'utilisateur à la liste des inscrits
        $event->addRegistred($user);

        // Persistance des modifications
        $entityManager->persist($event);
        $entityManager->flush();

        // Message de confirmation
        $this->addFlash('success', 'Vous êtes bien inscrit.');

        // Redirection vers le détail de l'événement
        return $this->redirectToRoute('event_detail', ['id' => $id]);
    }


    /**
     * Permet à un utilisateur de se désinscrire d'un événement.
     *
     * Cette méthode :
     * - Récupère l'événement via son identifiant
     * - Vérifie que l'événement existe
     * - Vérifie que l'utilisateur est authentifié
     * - Vérifie que l'utilisateur est bien inscrit à l'événement
     * - Retire l'utilisateur de la liste des inscrits
     * - Enregistre les modifications en base de données
     * - Ajoute un message de confirmation
     * - Redirige vers la page de détail de l'événement
     *
     * @param int $id Identifiant de l'événement
     * @param EventRepository $eventRepository Repository pour accéder aux événements
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités pour la persistance
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * Si l'événement n'existe pas
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * Si l'utilisateur n'est pas connecté ou non inscrit à l'événement
     *
     * @return Response Redirection vers la page détail de l'événement
     */
    #[Route('/desinscription/{id}', name: 'desinscription_event', requirements: ['id' => '\d+'])]
    public function desinscriptionEvent(
        int $id,
        EventRepository $eventRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {
        // Récupération de l'événement
        $event = $eventRepository->findEventById($id);

        // Récupération de l'utilisateur connecté
        $user = $this->getUser();

        // Vérifie que l'événement existe
        if (!$event) {
            throw $this->createNotFoundException('Événement introuvable.');
        }

        // Vérifie que l'utilisateur est connecté
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour vous inscrire.');
        }

        // Vérifie que l'utilisateur est bien inscrit à l'événement
        if (!$event->getRegistred()->contains($user)) {
            throw $this->createAccessDeniedException('Vous n\'êtes pas inscrit à cet événement.');
        }

        // Retire l'utilisateur de la liste des inscrits
        $event->removeRegistred($user);

        // Persistance des modifications
        $entityManager->persist($event);
        $entityManager->flush();

        // Message de confirmation
        $this->addFlash('success', 'Vous êtes bien désinscrit.');

        // Redirection vers le détail de l'événement
        return $this->redirectToRoute('event_detail', ['id' => $id]);
    }


}
