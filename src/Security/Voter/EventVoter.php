<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter de sécurité permettant de gérer les autorisations
 * sur l'entité Event.
 *
 * Ce voter contrôle les permissions suivantes :
 * - EVENT_VIEW : autorise l'affichage d'un événement
 * - EVENT_EDIT : autorise la modification d'un événement
 * - EVENT_DELETE : autorise la suppression d'un événement
 *
 * Règles appliquées :
 * - tout utilisateur connecté ou non peut consulter un événement
 * - seul l'organisateur de l'événement peut le modifier
 * - seul l'organisateur de l'événement peut le supprimer
 */
final class EventVoter extends Voter
{
    /**
     * Permission permettant de consulter un événement.
     */
    public const VIEW = 'EVENT_VIEW';

    /**
     * Permission permettant de modifier un événement.
     */
    public const EDIT = 'EVENT_EDIT';

    /**
     * Permission permettant de supprimer un événement.
     */
    public const DELETE = 'EVENT_DELETE';

    /**
     * Indique si ce voter prend en charge l'attribut et le sujet donnés.
     *
     * Le voter ne s'applique que si :
     * - l'attribut correspond à une des permissions gérées
     * - le sujet est une instance de Event
     *
     * @param string $attribute L'autorisation demandée.
     * @param mixed $subject L'objet sur lequel porte la vérification.
     *
     * @return bool True si ce voter peut traiter la demande, sinon false.
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && $subject instanceof Event;
    }

    /**
     * Vérifie si l'utilisateur courant possède l'autorisation demandée
     * sur l'événement fourni.
     *
     * Règles :
     * - VIEW : toujours autorisé
     * - EDIT : autorisé uniquement si l'utilisateur est l'organisateur
     * - DELETE : autorisé uniquement si l'utilisateur est l'organisateur
     *
     * @param string $attribute L'autorisation demandée.
     * @param mixed $subject L'objet concerné, attendu comme étant un Event.
     * @param TokenInterface $token Le token de sécurité de l'utilisateur courant.
     * @param Vote|null $vote Objet optionnel permettant d'ajouter une raison à la décision.
     *
     * @return bool True si l'accès est autorisé, sinon false.
     */
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null
    ): bool {
        $user = $token->getUser();

        // Refuse l'accès si aucun utilisateur authentifié n'est présent
        if (!$user instanceof User) {
            $vote?->addReason('Utilisateur non connecté');
            return false;
        }

        /** @var Event $event */
        $event = $subject;

        return match ($attribute) {
            self::VIEW => true, // Tout le monde peut consulter l'événement
            self::EDIT => $this->isOrganizer($event, $user, $vote),
            self::DELETE => $this->isOrganizer($event, $user, $vote),
            default => false,
        };
    }

    /**
     * Vérifie si l'utilisateur est l'organisateur de l'événement.
     *
     * @param Event $event L'événement concerné.
     * @param User $user L'utilisateur courant.
     * @param Vote|null $vote Objet optionnel permettant d'ajouter une explication à la décision.
     *
     * @return bool True si l'utilisateur est l'organisateur, sinon false.
     */
    private function isOrganizer(Event $event, User $user, ?Vote $vote = null): bool
    {
        if ($event->getOrganizer() === $user) {
            $vote?->addReason('Utilisateur est l’organisateur');
            return true;
        }

        $vote?->addReason('Utilisateur n’est pas l’organisateur');
        return false;
    }
}
