<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Security\Permission\EventPermissionChecker;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter chargé de gérer les autorisations liées aux événements.
 *
 * Cette classe délègue la logique métier de vérification des permissions
 * à {@see EventPermissionChecker}, tout en intégrant le mécanisme
 * de vote de sécurité de Symfony.
 */
final class EventVoter extends Voter
{
    /**
     * Permission permettant de consulter un événement.
     */
    public const VIEW = 'EVENT_VIEW';

    /**
     * Permission permettant de créer un événement.
     */
    public const CREATE = 'EVENT_CREATE';

    /**
     * Permission permettant de modifier un événement.
     */
    public const EDIT = 'EVENT_EDIT';

    /**
     * Permission permettant d'annuler un événement.
     */
    public const CANCEL = 'EVENT_CANCEL';

    /**
     * Permission permettant de s'inscrire à un événement.
     */
    public const REGISTER = 'EVENT_REGISTER';

    /**
     * Permission permettant de se désinscrire d'un événement.
     */
    public const UNREGISTER = 'EVENT_UNREGISTER';

    /**
     * @param Security $security Service de sécurité Symfony.
     * @param EventPermissionChecker $permissionChecker Service chargé de centraliser
     *                                                 la logique métier des permissions
     *                                                 sur les événements.
     */
    public function __construct(
        private readonly Security $security,
        private readonly EventPermissionChecker $permissionChecker,
    ) {
    }

    /**
     * Indique si ce voter prend en charge l'attribut et le sujet donnés.
     *
     * La permission de création ne nécessite pas de sujet.
     * Les autres permissions s'appliquent uniquement à une instance de {@see Event}.
     *
     * @param string $attribute L'attribut de sécurité demandé.
     * @param mixed $subject Le sujet sur lequel porte la vérification.
     *
     * @return bool Retourne true si ce voter peut traiter la demande, sinon false.
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute === self::CREATE) {
            return true;
        }

        return in_array($attribute, [
                self::VIEW,
                self::EDIT,
                self::CANCEL,
                self::REGISTER,
                self::UNREGISTER,
            ], true) && $subject instanceof Event;
    }

    /**
     * Effectue le vote pour un attribut donné sur un sujet donné.
     *
     * Si l'utilisateur possède le rôle administrateur, l'accès est automatiquement accordé.
     * Sinon, la décision est déléguée au service {@see EventPermissionChecker}.
     *
     * @param string $attribute L'attribut de sécurité à évaluer.
     * @param mixed $subject Le sujet concerné par le vote.
     * @param TokenInterface $token Le token d'authentification courant.
     * @param Vote|null $vote Objet optionnel permettant d'ajouter des raisons au vote.
     *
     * @return bool Retourne true si l'accès est autorisé, sinon false.
     */
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null
    ): bool {
        $user = $token->getUser();

//        if ($this->security->isGranted('ROLE_ADMIN')) {
//            $vote?->addReason('Accès autorisé : administrateur');
//            return true;
//        }

        return match ($attribute) {
            self::CREATE => $this->permissionChecker->canCreate($user, $vote),

            self::VIEW => $subject instanceof Event
                && $this->permissionChecker->canView($subject, $user, $vote),

            self::EDIT => $subject instanceof Event
                && $this->permissionChecker->canEdit($subject, $user, $vote),

            self::CANCEL => $subject instanceof Event
                && $this->permissionChecker->canCancel($subject, $user, $vote),

            self::REGISTER => $subject instanceof Event
                && $this->permissionChecker->canRegister($subject, $user, $vote),

            self::UNREGISTER => $subject instanceof Event
                && $this->permissionChecker->canUnregister($subject, $user, $vote),

            default => false,
        };
    }
}
