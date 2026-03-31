<?php

namespace App\Security\Permission;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

/**
 * Service centralisant les règles métier d'autorisation des événements.
 *
 * Cette classe contient toute la logique métier liée aux permissions.
 * Elle est utilisée par les Voters pour prendre des décisions d'accès.
 *
 * Avantage : séparation claire entre sécurité Symfony et logique métier.
 */
final class EventPermissionChecker
{
    /**
     * Vérifie si l'utilisateur peut créer un événement.
     *
     * Règle :
     * - l'utilisateur doit être connecté
     *
     * @param mixed $user L'utilisateur courant (peut être null ou autre type)
     * @param Vote|null $vote Permet d'ajouter une explication à la décision
     *
     * @return bool True si autorisé, sinon false
     */
    public function canCreate(mixed $user, ?Vote $vote = null): bool
    {
        // Vérifie que l'utilisateur est bien authentifié
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
     * Règle actuelle :
     * - tous les événements sont visibles
     *
     * @param Event $event L'événement concerné
     * @param mixed $user L'utilisateur courant
     * @param Vote|null $vote Permet d'ajouter une raison
     *
     * @return bool
     */
    public function canView(Event $event, mixed $user, ?Vote $vote = null): bool
    {
        // Pour l'instant, aucun contrôle : accès libre
        $vote?->addReason('Consultation autorisée');
        return true;
    }

    /**
     * Vérifie si l'utilisateur peut modifier un événement.
     *
     * Règles :
     * - l'utilisateur doit être connecté
     * - l'utilisateur doit être soit administrateur, soit organisateur de l'événement
     * - modification refusée si l'événement est déjà commencé ou passé
     *
     * @param Event $event L'événement concerné
     * @param mixed $user L'utilisateur courant
     * @param Vote|null $vote Permet d'ajouter une raison
     *
     * @return bool
     */
    public function canEdit(Event $event, mixed $user, ?Vote $vote = null): bool
    {
        // Vérifie que l'utilisateur est connecté
        if (!$user instanceof User) {
            $vote?->addReason('Modification refusée : utilisateur non connecté');
            return false;
        }

        // Vérifie si l'utilisateur possède le rôle administrateur
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles(), true);

        // Vérifie si l'utilisateur est l'organisateur de l'événement
        $isOrganizer = $this->isOrganizer($event, $user);

        // Refuse l'action si l'utilisateur n'est ni administrateur ni organisateur
        if (!$isAdmin && !$isOrganizer) {
            $vote?->addReason('Annulation refusée : ni administrateur ni organisateur');
            return false;
        }

        // Vérifie que l'événement n'a pas déjà commencé
        $dateStart = $event->getDateStart();
        if ($dateStart !== null && $dateStart < new \DateTime()) {
            $vote?->addReason('Modification refusée : événement déjà commencé ou passé');
            return false;
        }

        $vote?->addReason(
            $isAdmin
                ? 'Annulation autorisée : administrateur'
                : 'Annulation autorisée : utilisateur organisateur'
        );
        return true;
    }

    /**
     * Vérifie si l'utilisateur peut annuler un événement.
     *
     * Règles métier appliquées :
     * - l'utilisateur doit être connecté
     * - l'utilisateur doit être soit administrateur, soit organisateur de l'événement
     * - l'annulation est refusée si l'événement a déjà commencé ou est passé
     *
     * @param Event $event L'événement concerné par la demande d'annulation.
     * @param mixed $user L'utilisateur courant. Peut-être un objet User ou une autre valeur si non authentifié.
     * @param Vote|null $vote Objet optionnel permettant d'ajouter une raison à la décision.
     *
     * @return bool Retourne true si l'annulation est autorisée, sinon false.
     */
    public function canCancel(Event $event, mixed $user, ?Vote $vote = null): bool
    {
        // Vérifie que l'utilisateur est bien connecté et correspond à une entité User
        if (!$user instanceof User) {
            $vote?->addReason('Annulation refusée : utilisateur non connecté');
            return false;
        }

        // Vérifie si l'utilisateur possède le rôle administrateur
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles(), true);

        // Vérifie si l'utilisateur est l'organisateur de l'événement
        $isOrganizer = $this->isOrganizer($event, $user);

        // Refuse l'action si l'utilisateur n'est ni administrateur ni organisateur
        if (!$isAdmin && !$isOrganizer) {
            $vote?->addReason('Annulation refusée : ni administrateur ni organisateur');
            return false;
        }

        // Récupère la date de début de l'événement pour empêcher l'annulation après son démarrage
        $dateStart = $event->getDateStart();
        if ($dateStart !== null && $dateStart < new \DateTime()) {
            $vote?->addReason('Annulation refusée : événement déjà commencé ou passé');
            return false;
        }

        // Ajoute une raison différente selon que l'autorisation vient du rôle admin
        // ou du fait que l'utilisateur est l'organisateur
        $vote?->addReason(
            $isAdmin
                ? 'Annulation autorisée : administrateur'
                : 'Annulation autorisée : utilisateur organisateur'
        );

        return true;
    }

    /**
     * Vérifie si l'utilisateur peut s'inscrire à un événement.
     *
     * Règles :
     * - utilisateur connecté obligatoire
     * - date limite non dépassée
     * - utilisateur non déjà inscrit
     * - utilisateur ≠ organisateur
     * - événement non complet
     *
     * @param Event $event L'événement concerné
     * @param mixed $user L'utilisateur courant
     * @param Vote|null $vote Permet d'ajouter une raison
     *
     * @return bool
     */
    public function canRegister(Event $event, mixed $user, ?Vote $vote = null): bool
    {
        // Vérifie que l'utilisateur est connecté
        if (!$user instanceof User) {
            $vote?->addReason('Inscription refusée : utilisateur non connecté');
            return false;
        }

        // Vérifie la date limite d'inscription
        $deadline = $event->getDeadline();
        if ($deadline !== null && $deadline < new \DateTime()) {
            $vote?->addReason('Inscription refusée : date limite dépassée');
            return false;
        }

        // Empêche l'organisateur de s'inscrire
        if ($this->isOrganizer($event, $user)) {
            $vote?->addReason('Inscription refusée : l’organisateur ne peut pas s’inscrire');
            return false;
        }

        // Vérifie si l'utilisateur est déjà inscrit
        if ($event->getRegistred()->contains($user)) {
            $vote?->addReason('Inscription refusée : utilisateur déjà inscrit');
            return false;
        }

        // Vérifie si l'événement est complet
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
     * Règles :
     * - utilisateur connecté obligatoire
     * - utilisateur déjà inscrit
     * - événement non commencé
     *
     * @param Event $event L'événement concerné
     * @param mixed $user L'utilisateur courant
     * @param Vote|null $vote Permet d'ajouter une raison
     *
     * @return bool
     */
    public function canUnregister(Event $event, mixed $user, ?Vote $vote = null): bool
    {
        // Vérifie que l'utilisateur est connecté
        if (!$user instanceof User) {
            $vote?->addReason('Désinscription refusée : utilisateur non connecté');
            return false;
        }

        // Vérifie que l'utilisateur est bien inscrit
        if (!$event->getRegistred()->contains($user)) {
            $vote?->addReason('Désinscription refusée : utilisateur non inscrit');
            return false;
        }

        // Empêche la désinscription après le début de l'événement
        $dateStart = $event->getDateStart();
        if ($dateStart !== null && $dateStart < new \DateTime()) {
            $vote?->addReason('Désinscription refusée : événement déjà commencé ou passé');
            return false;
        }

        $vote?->addReason('Désinscription autorisée');
        return true;
    }

    /**
     * Vérifie si un utilisateur est l'organisateur de l'événement.
     *
     * @param Event $event L'événement concerné
     * @param User $user L'utilisateur courant
     *
     * @return bool True si l'utilisateur est l'organisateur
     */
    private function isOrganizer(Event $event, User $user): bool
    {
        // Récupère l'organisateur de l'événement
        $organizer = $event->getOrganizer();

        // Compare les identifiants pour éviter les problèmes de référence d'objet
        return $organizer !== null && $organizer->getId() === $user->getId();
    }
}
