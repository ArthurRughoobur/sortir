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

/**
 * Contrôleur chargé de la gestion des événements.
 *
 * Il regroupe les actions principales liées aux événements :
 * - affichage de la liste
 * - affichage du détail
 * - création / modification
 * - inscription / désinscription
 */
final class EventController extends AbstractController
{
    /**
     * Affiche la page principale des événements avec recherche et filtrage.
     *
     * Cette méthode :
     * - vérifie que l'utilisateur est authentifié
     * - met à jour certains statuts d'événements automatiquement
     * - initialise et traite le formulaire de recherche
     * - récupère les événements selon les filtres sélectionnés
     * - filtre également les événements du campus de l'utilisateur
     *
     * @param EventRepository $eventRepository Repository des événements
     * @param Request $request Requête HTTP courante
     * @param UpdateEventStatus $eventStatus Service de mise à jour des statuts d'événements
     *
     * @return Response Réponse HTTP contenant la page principale
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * Si l'utilisateur n'est pas authentifié
     */
    #[Route('/', name: 'main_event')]
    public function mainEvent(
        EventRepository   $eventRepository,
        Request           $request,
        UpdateEventStatus $eventStatus,
    ): Response
    {

        $eventStatus->syncAllEventStatuses();


        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $eventSearch = new EventSearch();
        $eventSearch->setCampus($user->getCampus());
        $eventFormSearch = $this->createForm(EventSearchType::class, $eventSearch);
        $eventFormSearch->handleRequest($request);

        $events = $eventRepository->findEventList($eventSearch, $user);

        return $this->render('event/index.html.twig', [
            'events' => $events,
            'eventFormSearch' => $eventFormSearch->createView(),
        ]);
    }

    /**
     * Affiche le détail d'un événement.
     *
     * Cette méthode :
     * - met à jour les statuts selon la capacité maximale
     * - récupère l'événement demandé
     * - vérifie le droit de consultation
     * - affiche la page de détail
     *
     * @param int $id Identifiant de l'événement
     * @param EventRepository $eventRepository Repository des événements
     * @param UpdateEventStatus $eventStatusMaxInscription Service de synchronisation des statuts
     * @param Event $event Entité injectée automatiquement par Symfony
     *
     * @return Response Réponse HTTP contenant la page de détail
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * Si l'événement n'existe pas
     */
    #[Route('/detail/{id}', name: 'event_detail', requirements: ['id' => '\d+'])]
    public function detailEvent(
        int               $id,
        EventRepository   $eventRepository,
        UpdateEventStatus $eventStatusMaxInscription,
        Event             $event
    ): Response
    {


        // Met à jour les statuts des événements selon leur capacité
        $eventStatusMaxInscription->syncAllEventStatuses();

        // Recharge l'événement avec ses relations utiles via le repository personnalisé
        $event = $eventRepository->findEventById($id);

        // Vérifie l'existence de l'événement avant de poursuivre
        if (!$event) {
            throw $this->createNotFoundException('Événement introuvable.');
        }
        // Vérifie une première fois l'accès sur l'entité injectée par Symfony
        $this->denyAccessUnlessGranted(EventVoter::VIEW, $event);
        // Retourne la vue de détail
        return $this->render('event/detailEvent.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * Affiche la page de création ou de modification d'un événement.
     *
     * Cette méthode gère deux routes :
     * - création d'un nouvel événement
     * - modification d'un événement existant
     *
     * Règles :
     * - si un identifiant est présent, on charge l'événement et on vérifie le droit d'édition
     * - sinon, on vérifie le droit de création et on initialise un nouvel événement
     *
     * @param Request $request Requête HTTP courante
     * @param EventRepository $eventRepository Repository des événements
     *
     * @return Response Réponse HTTP contenant la page de formulaire
     */
    #[Route('/create_event', name: 'create_event')]
    #[Route('/update_event/{id}', name: 'update_event', requirements: ['id' => '\d+'])]
    public function createEvent(
        Request         $request,
        EventRepository $eventRepository,
    ): Response
    {
        // Récupère l'identifiant d'événement présent dans la route, s'il existe
        $id = $request->attributes->get('id');

        if ($id !== null) {
            // Cas modification : recherche de l'événement existant
            $event = $eventRepository->find($id);

            // Vérifie que l'événement existe bien
            if (!$event) {
                throw $this->createNotFoundException('Événement introuvable.');
            }

            // Vérifie que l'utilisateur a le droit de modifier cet événement
            $this->denyAccessUnlessGranted(EventVoter::EDIT, $event);
        } else {
            // Cas création : vérifie que l'utilisateur a le droit de créer
            $this->denyAccessUnlessGranted(EventVoter::CREATE);

            // Initialise un nouvel événement
            $event = new Event();

            // Définit l'utilisateur courant comme organisateur
            $event->setOrganizer($this->getUser());
        }


        return $this->render('event/createEvent.html.twig', [
            'event' => $event,
        ]);

    }

    /**
     * Inscrit l'utilisateur connecté à un événement.
     *
     * Cette méthode :
     * - vérifie le droit d'inscription
     * - recharge l'événement demandé
     * - ajoute l'utilisateur à la liste des inscrits
     * - enregistre la modification
     * - ajoute un message flash
     * - redirige vers le détail de l'événement
     *
     * @param int $id Identifiant de l'événement
     * @param EventRepository $eventRepository Repository des événements
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param Event $event Entité injectée pour le contrôle d'accès
     *
     * @return Response Redirection vers la page de détail de l'événement
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * Si l'événement n'existe pas
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * Si l'utilisateur n'est pas autorisé à s'inscrire
     */
    #[Route('/inscription/{id}', name: 'inscription_event', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function inscriptionEvent(
        int                    $id,
        EventRepository        $eventRepository,
        EntityManagerInterface $entityManager,
        Event                  $event,
    ): Response
    {


        // Recharge l'événement avec ses relations complètes
        $event = $eventRepository->findEventById($id);

        // Vérifie que l'événement existe
        if (!$event) {
            throw $this->createNotFoundException('Événement introuvable.');
        }
        // Vérifie le droit d'inscription sur l'événement injecté
        $this->denyAccessUnlessGranted(EventVoter::REGISTER, $event);
        // Récupère l'utilisateur connecté
        /** @var User $user */
        $user = $this->getUser();

        // Ajoute l'utilisateur à la liste des inscrits
        $event->addRegistred($user);

        // Enregistre la modification en base de données
        $entityManager->persist($event);
        $entityManager->flush();

        // Ajoute un message flash de confirmation
        $this->addFlash('success', 'Vous êtes bien inscrit.');

        // Redirige vers la page de détail
        return $this->redirectToRoute('event_detail', ['id' => $id]);
    }

    /**
     * Désinscrit l'utilisateur connecté d'un événement.
     *
     * Cette méthode :
     * - vérifie le droit de désinscription
     * - recharge l'événement demandé
     * - retire l'utilisateur de la liste des inscrits
     * - enregistre la modification
     * - ajoute un message flash
     * - redirige vers le détail de l'événement
     *
     * @param int $id Identifiant de l'événement
     * @param EventRepository $eventRepository Repository des événements
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités Doctrine
     * @param Event $event Entité injectée pour le contrôle d'accès
     *
     * @return Response Redirection vers la page de détail de l'événement
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * Si l'événement n'existe pas
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * Si l'utilisateur n'est pas autorisé à se désinscrire
     */
    #[Route('/desinscription/{id}', name: 'desinscription_event', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function desinscriptionEvent(
        int                    $id,
        EventRepository        $eventRepository,
        EntityManagerInterface $entityManager,
        Event                  $event
    ): Response
    {


        // Recharge l'événement complet via le repository
        $event = $eventRepository->findEventById($id);

        // Vérifie que l'événement existe
        if (!$event) {
            throw $this->createNotFoundException('Événement introuvable.');
        }
        // Vérifie le droit de désinscription sur l'événement injecté
        $this->denyAccessUnlessGranted(EventVoter::UNREGISTER, $event);
        // Récupère l'utilisateur connecté
        /** @var User $user */
        $user = $this->getUser();

        // Retire l'utilisateur de la liste des inscrits
        $event->removeRegistred($user);

        // Enregistre la modification en base de données
        $entityManager->persist($event);
        $entityManager->flush();

        // Ajoute un message flash de confirmation
        $this->addFlash('success', 'Vous êtes bien désinscrit.');

        // Redirige vers la page de détail
        return $this->redirectToRoute('event_detail', ['id' => $id]);
    }
}
