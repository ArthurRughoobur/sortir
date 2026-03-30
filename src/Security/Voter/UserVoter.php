<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Security\Permission\UserPermissionChecker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter chargé de gérer les autorisations liées aux utilisateurs.
 *
 * Cette classe permet de déterminer si un utilisateur peut effectuer
 * certaines actions sur un autre utilisateur (ou lui-même).
 *
 * La logique métier est déléguée à {@see UserPermissionChecker}.
 */
final class UserVoter extends Voter
{
    /**
     * Permission permettant de modifier un utilisateur.
     */
    public const EDIT = 'USER_EDIT';

    /**
     * Initialise le voter avec le service de gestion des permissions.
     *
     * @param UserPermissionChecker $userPermissionChecker Service contenant
     *                                                     les règles métier liées aux utilisateurs.
     */
    public function __construct(
        private readonly UserPermissionChecker $userPermissionChecker,
    ) {
    }

    /**
     * Détermine si ce voter peut gérer la demande d'autorisation.
     *
     * Ici, on ne gère que l'attribut {@see self::EDIT} et uniquement
     * si le sujet est une instance de {@see User}.
     *
     * @param string $attribute L'attribut de sécurité demandé.
     * @param mixed $subject Le sujet sur lequel porte la vérification.
     *
     * @return bool True si le voter supporte la demande, sinon false.
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // On vérifie que l'action demandée est EDIT et que le sujet est bien un User
        return $attribute === self::EDIT && $subject instanceof User;
    }

    /**
     * Effectue la vérification des permissions.
     *
     * Cette méthode est appelée uniquement si {@see supports()} retourne true.
     *
     * @param string $attribute L'attribut de sécurité à évaluer.
     * @param mixed $subject L'utilisateur cible de l'action.
     * @param TokenInterface $token Le token de sécurité contenant l'utilisateur connecté.
     * @param Vote|null $vote Permet d'ajouter des raisons au refus/acceptation.
     *
     * @return bool True si l'accès est autorisé, sinon false.
     */
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null
    ): bool {
        // Récupère l'utilisateur actuellement connecté
        $currentUser = $token->getUser();

        // Vérifie que l'utilisateur est bien authentifié et de type User
        if (!$currentUser instanceof User) {
            // Ajoute une raison au vote (utile pour debug ou logs Symfony)
            $vote?->addReason('Utilisateur non connecté');
            return false;
        }

        /** @var User $targetUser */
        // L'utilisateur cible sur lequel porte l'action
        $targetUser = $subject;

        // Délègue la logique métier au service dédié
        return match ($attribute) {
            self::EDIT => $this->userPermissionChecker->canEdit($currentUser, $targetUser, $vote),

            // Sécurité : refuse toute autre action non prévue
            default => false,
        };
    }
}
