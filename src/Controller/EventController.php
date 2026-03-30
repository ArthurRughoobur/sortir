<?php

namespace App\Controller;

use App\Entity\Event;
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
     * @return Response Réponse HTTP contenant la page des événements
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * Si l'utilisateur n'est pas authentifié
     *
     */
    #[Route('/', name: 'main_event')]
    public function mainEvent(
        EventRepository   $eventRepository,
        Request           $request,
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
        int               $id,
        EventRepository   $eventRepository,
        UpdateEventStatus $eventStatusMaxInscription,
        Event             $event
    ): Response
    {
        $this->denyAccessUnlessGranted(EventVoter::VIEW, $event);

        // Mise à jour des statuts des événements selon leur capacité
        $eventStatusMaxInscription->syncEventStatusesWithCapacity();

        // Récupération de l'événement
        $event = $eventRepository->findEventById($id);
        $this->denyAccessUnlessGranted(EventVoter::VIEW, $event);

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
    public function createEvent(
        Request         $request,
        EventRepository $eventRepository,
    ): Response
    {
        $id = $request->attributes->get('id');
        if ($id !== null) {
            $event = $eventRepository->find($id);

            if (!$event) {
                throw $this->createNotFoundException('Événement introuvable.');
            }

            $this->denyAccessUnlessGranted(EventVoter::EDIT, $event);
        } else {
            $this->denyAccessUnlessGranted(EventVoter::CREATE);
            $event = new Event();
            $event->setOrganizer($this->getUser());
        }
//            if ($id !== null && $event->getOrganizer() !== $this->getUser()) {
//                throw $this->createAccessDeniedException('Vous ne pouvez pas modifier cet événement.');
//            }
//            $this->denyAccessUnlessGranted(EventVoter::EDIT, $event);



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
     * Inscrit un utilisateur à un événement.
     *
     * Cette méthode :
     * - Vérifie les droits d'inscription via le voter
     * - Récupère l'événement à partir de son identifiant
     * - Vérifie que l'événement existe
     * - Récupère l'utilisateur actuellement authentifié
     * - Ajoute l'utilisateur à la liste des inscrits
     * - Persiste les modifications en base de données
     * - Ajoute un message flash de confirmation
     * - Redirige vers la page de détail de l'événement
     *
     * @param int $id Identifiant de l'événement
     * @param EventRepository $eventRepository Accès aux données des événements
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités
     * @param Event $event Entité injectée (utilisée pour le contrôle d'accès)
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException Si l'événement n'existe pas
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException Si l'utilisateur n'est pas autorisé à s'inscrire
     *
     * @return Response Redirection vers la page de détail de l'événement
     */
    #[Route('/inscription/{id}', name: 'inscription_event', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function inscriptionEvent(
        int                    $id,
        EventRepository        $eventRepository,
        EntityManagerInterface $entityManager,
        Event                  $event,
    ): Response
    {
        $this->denyAccessUnlessGranted(EventVoter::REGISTER, $event);

        // Récupération de l'événement
        $event = $eventRepository->findEventById($id);

        // Vérifie que l'événement existe
        if (!$event) {
            throw $this->createNotFoundException('Événement introuvable.');
        }
        // Récupération de l'utilisateur connecté
        /** @var User $user */
        $user = $this->getUser();

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
     * Désinscrit un utilisateur d'un événement.
     *
     * Cette méthode :
     * - Vérifie les droits de désinscription via le voter
     * - Récupère l'événement à partir de son identifiant
     * - Vérifie que l'événement existe
     * - Récupère l'utilisateur actuellement authentifié
     * - Retire l'utilisateur de la liste des inscrits
     * - Persiste les modifications en base de données
     * - Ajoute un message flash de confirmation
     * - Redirige vers la page de détail de l'événement
     *
     * @param int $id Identifiant de l'événement
     * @param EventRepository $eventRepository Accès aux données des événements
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités
     * @param Event $event Entité injectée (utilisée pour le contrôle d'accès)
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException Si l'événement n'existe pas
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException Si l'utilisateur n'est pas autorisé à se désinscrire
     *
     * @return Response Redirection vers la page de détail de l'événement
     */
    #[Route('/desinscription/{id}', name: 'desinscription_event', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function desinscriptionEvent(
        int                    $id,
        EventRepository        $eventRepository,
        EntityManagerInterface $entityManager,
        Event                  $event
    ): Response
    {
        $this->denyAccessUnlessGranted(EventVoter::UNREGISTER, $event);

        // Récupération de l'événement
        $event = $eventRepository->findEventById($id);

        // Vérifie que l'événement existe
        if (!$event) {
            throw $this->createNotFoundException('Événement introuvable.');
        }

        // Récupération de l'utilisateur connecté
        /** @var User $user */
        $user = $this->getUser();

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
