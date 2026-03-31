<?php

namespace App\Security\Permission;

use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

/**
 * Service gérant les règles métier liées aux permissions des utilisateurs.
 *
 * Cette classe est utilisée par les Voters pour déterminer si une action
 * est autorisée entre deux utilisateurs.
 */
class UserPermissionChecker
{
    /**
     * Vérifie si un utilisateur peut modifier un profil utilisateur.
     *
     * Règle actuelle :
     * - un utilisateur peut uniquement modifier son propre profil
     *
     * @param User $user L'utilisateur actuellement connecté
     * @param User $targetUser L'utilisateur cible de l'action
     * @param Vote|null $vote Permet d'ajouter une raison à la décision
     *
     * @return bool True si autorisé, sinon false
     */
    public function canEdit(User $user,User $targetUser, ?Vote $vote = null ): bool
    {

        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles(), true);
        $isUser = $targetUser->getId() === $user->getId();

        // Vérifie si l'utilisateur tente de modifier son propre profil
        if ($isUser) {
            $vote?->addReason('L’utilisateur modifie son propre profil');
            return true;
        }
        if($isAdmin){
            $vote?->addReason('L\'admin modifie un utilisateur');
            return true;
        }

        // Refus si l'utilisateur tente de modifier un autre profil
        $vote?->addReason('L’utilisateur tente de modifier le profil d’un autre utilisateur');
        return false;
    }
}
