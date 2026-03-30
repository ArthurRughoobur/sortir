<?php

namespace App\Security\Permission;

use App\Entity\User;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class UserPermissionChecker
{
    public function canEdit(User $currentUser, User $targetUser, ?Vote $vote = null): bool
    {
        if ($targetUser->getId() === $currentUser->getId()) {
            $vote?->addReason('L’utilisateur modifie son propre profil');
            return true;
        }

        $vote?->addReason('L’utilisateur tente de modifier le profil d’un autre utilisateur');
        return false;
    }
}
