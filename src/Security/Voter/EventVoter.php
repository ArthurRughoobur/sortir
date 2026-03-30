<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Security\Permission\EventPermissionChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter chargé de gérer les autorisations liées aux événements.
 *
 * Cette classe s'intègre au système de sécurité de Symfony afin de déterminer
 * si un utilisateur peut effectuer une action sur un événement.
 *
 * La logique métier détaillée n'est pas écrite directement ici :
 * elle est déléguée au service {@see EventPermissionChecker}.
 *
 * Ce voter joue donc principalement un rôle de "pont" entre
 * le mécanisme de vote Symfony et les règles métier applicatives.
 */
final class EventVoter extends Voter
{
    /**
     * Permission permettant de consulter un événement.
     */
    public const VIEW = 'EVENT_VIEW';

    /**
     * Permission permettant de créer un événement.
     *
     * Cette permission ne nécessite pas d'objet {@see Event} en sujet,
     * car on vérifie un droit global de création.
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
     * Initialise le voter avec les services nécessaires.
     *
     * @param Security $security Service de sécurité Symfony.
     * @param EventPermissionChecker $permissionChecker Service centralisant
     *                                                 les règles métier de permissions
     *                                                 sur les événements.
     */
    public function __construct(
        private readonly EventPermissionChecker $permissionChecker,
    ) {
    }

    /**
     * Détermine si ce voter sait traiter l'attribut et le sujet reçus.
     *
     * Symfony appelle cette méthode avant {@see voteOnAttribute()} afin de savoir
     * si ce voter est concerné par la demande d'autorisation.
     *
     * - La permission {@see self::CREATE} ne nécessite pas de sujet.
     * - Toutes les autres permissions nécessitent un sujet de type {@see Event}.
     *
     * @param string $attribute L'attribut de sécurité demandé.
     * @param mixed $subject Le sujet sur lequel porte la vérification.
     *
     * @return bool Retourne true si ce voter peut traiter la demande, sinon false.
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Le droit de création est global : il ne dépend pas d'un événement précis.
        if ($attribute === self::CREATE) {
            return true;
        }

        // Pour toutes les autres actions, on vérifie :
        // 1. que l'attribut fait partie de ceux gérés par ce voter
        // 2. que le sujet est bien une instance de Event
        return in_array($attribute, [
                self::VIEW,
                self::EDIT,
                self::CANCEL,
                self::REGISTER,
                self::UNREGISTER,
            ], true) && $subject instanceof Event;
    }

    /**
     * Prend la décision d'autorisation pour un attribut donné.
     *
     * Cette méthode est appelée uniquement si {@see supports()} a retourné true.
     *
     * La décision est ensuite déléguée à {@see EventPermissionChecker},
     * qui contient la logique métier spécifique à chaque action.
     *
     * @param string $attribute L'attribut de sécurité à évaluer.
     * @param mixed $subject Le sujet concerné par le vote.
     * @param TokenInterface $token Le token d'authentification de l'utilisateur courant.
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
        // Récupère l'utilisateur actuellement authentifié depuis le token de sécurité.
        $user = $token->getUser();

        // Oriente la décision selon l'attribut demandé.
        // Chaque vérification métier est externalisée dans EventPermissionChecker.
        return match ($attribute) {
            // La création ne dépend pas d'un objet Event existant.
            self::CREATE => $this->permissionChecker->canCreate($user, $vote),

            // Vérifie d'abord que le sujet est bien un Event avant d'appliquer la règle métier.
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

            // Sécurité supplémentaire : tout attribut non prévu est refusé.
            default => false,
        };
    }
}
