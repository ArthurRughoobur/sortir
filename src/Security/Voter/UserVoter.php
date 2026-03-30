<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Security\Permission\UserPermissionChecker;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class UserVoter extends Voter
{
    public const EDIT = 'USER_EDIT';

    public function __construct(
        private readonly UserPermissionChecker $userPermissionChecker,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::EDIT && $subject instanceof User;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null
    ): bool {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof User) {
            $vote?->addReason('Utilisateur non connecté');
            return false;
        }


        /** @var User $targetUser */
        $targetUser = $subject;

        return match ($attribute) {
            self::EDIT => $this->userPermissionChecker->canEdit($currentUser, $targetUser, $vote),
            default => false,
        };
    }
}
