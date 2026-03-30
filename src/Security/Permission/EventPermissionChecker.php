<?php

namespace App\Security\Permission;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

/**
 * Service centralisant les règles métier d'autorisation des événements.
 */
final class EventPermissionChecker
{
    /**
     * Vérifie si l'utilisateur peut créer un événement.
     *
     * @param mixed $user L'utilisateur courant
     * @param Vote|null $vote Permet d'ajouter une raison à la décision
     *
     * @return bool
     */
    public function canCreate(mixed $user, ?Vote $vote = null): bool
    {
        if (!$user instanceof User) {
            $vote?->addReason('Création refusée : utilisateur non connecté');
            return false;
        }

        $vote?->addReason('Création autorisée : utilisateur connecté');
        return true;
    }

    /**
     * Vérifie si un événement peut être consulté.
     *
     * @param Event $event L'événement concerné
     * @param mixed $user L'utilisateur courant
     * @param Vote|null $vote Permet d'ajouter une raison à la décision
     *
     * @return bool
     */
    public function canView(Event $event, mixed $user, ?Vote $vote = null): bool
    {
        $vote?->addReason('Consultation autorisée');
        return true;
    }

    /**
     * Vérifie si l'utilisateur peut modifier un événement.
     *
     * Règles proposées :
     * - utilisateur connecté obligatoire
     * - seul l'organisateur peut modifier
     * - modification refusée si l'événement est déjà passé
     *
     * @param Event $event L'événement concerné
     * @param mixed $user L'utilisateur courant
     * @param Vote|null $vote Permet d'ajouter une raison
     *
     * @return bool
     */
    public function canEdit(Event $event, mixed $user, ?Vote $vote = null): bool
    {
        if (!$user instanceof User) {
            $vote?->addReason('Modification refusée : utilisateur non connecté');
            return false;
        }

        if (!$this->isOrganizer($event, $user)) {
            $vote?->addReason('Modification refusée : utilisateur non organisateur');
            return false;
        }

        $dateStart = $event->getDateStart();
        if ($dateStart !== null && $dateStart < new \DateTime()) {
            $vote?->addReason('Modification refusée : événement déjà commencé ou passé');
            return false;
        }

        $vote?->addReason('Modification autorisée : utilisateur organisateur');
        return true;
    }

    /**
     * Vérifie si l'utilisateur peut annuler un événement.
     *
     * Règles proposées :
     * - utilisateur connecté obligatoire
     * - seul l'organisateur peut annuler
     * - refus si l'événement est déjà passé
     *
     * @param Event $event L'événement concerné
     * @param mixed $user L'utilisateur courant
     * @param Vote|null $vote Permet d'ajouter une raison
     *
     * @return bool
     */
    public function canCancel(Event $event, mixed $user, ?Vote $vote = null): bool
    {
        if (!$user instanceof User) {
            $vote?->addReason('Annulation refusée : utilisateur non connecté');
            return false;
        }

        if (!$this->isOrganizer($event, $user)) {
            $vote?->addReason('Annulation refusée : utilisateur non organisateur');
            return false;
        }

        $dateStart = $event->getDateStart();
        if ($dateStart !== null && $dateStart < new \DateTime()) {
            $vote?->addReason('Annulation refusée : événement déjà commencé ou passé');
            return false;
        }

        $vote?->addReason('Annulation autorisée : utilisateur organisateur');
        return true;
    }

    /**
     * Vérifie si l'utilisateur peut s'inscrire à un événement.
     *
     * Règles proposées :
     * - utilisateur connecté obligatoire
     * - impossible si la date limite est dépassée
     * - impossible si l'utilisateur est déjà inscrit
     * - impossible si l'utilisateur est l'organisateur
     * - impossible si l'événement est complet
     *
     * @param Event $event L'événement concerné
     * @param mixed $user L'utilisateur courant
     * @param Vote|null $vote Permet d'ajouter une raison
     *
     * @return bool
     */
    public function canRegister(Event $event, mixed $user, ?Vote $vote = null): bool
    {
        if (!$user instanceof User) {
            $vote?->addReason('Inscription refusée : utilisateur non connecté');
            return false;
        }

        $deadline = $event->getDeadline();
        if ($deadline !== null && $deadline < new \DateTime()) {
            $vote?->addReason('Inscription refusée : date limite dépassée');
            return false;
        }

        if ($this->isOrganizer($event, $user)) {
            $vote?->addReason('Inscription refusée : l’organisateur ne peut pas s’inscrire');
            return false;
        }

        if ($event->getRegistred()->contains($user)) {
            $vote?->addReason('Inscription refusée : utilisateur déjà inscrit');
            return false;
        }

        $maxInscription = $event->getMaxIscription();
        if (
            $maxInscription !== null
            && $event->getRegistred()->count() >= $maxInscription
        ) {
            $vote?->addReason('Inscription refusée : événement complet');
            return false;
        }

        $vote?->addReason('Inscription autorisée');
        return true;
    }

    /**
     * Vérifie si l'utilisateur peut se désinscrire d'un événement.
     *
     * Règles proposées :
     * - utilisateur connecté obligatoire
     * - l'utilisateur doit être déjà inscrit
     * - désinscription refusée si l'événement a commencé
     *
     * @param Event $event L'événement concerné
     * @param mixed $user L'utilisateur courant
     * @param Vote|null $vote Permet d'ajouter une raison
     *
     * @return bool
     */
    public function canUnregister(Event $event, mixed $user, ?Vote $vote = null): bool
    {
        if (!$user instanceof User) {
            $vote?->addReason('Désinscription refusée : utilisateur non connecté');
            return false;
        }

        if (!$event->getRegistred()->contains($user)) {
            $vote?->addReason('Désinscription refusée : utilisateur non inscrit');
            return false;
        }

        $dateStart = $event->getDateStart();
        if ($dateStart !== null && $dateStart < new \DateTime()) {
            $vote?->addReason('Désinscription refusée : événement déjà commencé ou passé');
            return false;
        }

        $vote?->addReason('Désinscription autorisée');
        return true;
    }

    /**
     * Vérifie si l'utilisateur courant est l'organisateur de l'événement.
     *
     * @param Event $event L'événement concerné
     * @param User $user L'utilisateur courant
     *
     * @return bool
     */
    private function isOrganizer(Event $event, User $user): bool
    {
        $organizer = $event->getOrganizer();

        return $organizer !== null && $organizer->getId() === $user->getId();
    }
}
