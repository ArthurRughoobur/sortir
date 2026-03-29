<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class EventVoter extends Voter
{
    public const VIEW = 'EVENT_VIEW';
    public const EDIT = 'EVENT_EDIT';
    public const DELETE = 'EVENT_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && $subject instanceof Event;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
        ?Vote $vote = null
    ): bool {
        $user = $token->getUser();

        if (!$user instanceof User) {
            $vote?->addReason('Utilisateur non connecté');
            return false;
        }

        /** @var Event $event */
        $event = $subject;

        return match ($attribute) {
            self::VIEW => true, // tout le monde peut voir
            self::EDIT => $this->isOrganizer($event, $user, $vote),
            self::DELETE => $this->isOrganizer($event, $user, $vote),
            default => false,
        };
    }

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
