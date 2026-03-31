<?php

namespace App\Service;

use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class UpdateEventStatus
{

    public function __construct(
        private EventRepository        $eventRepository,
        private StatusRepository       $statusRepository,
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function syncAllEventStatuses(): void
    {
        $statuses = $this->getStatuses();

        $events = $this->eventRepository->findAllForStatusUpdate();

        $now = new \DateTimeImmutable();
        $oneMonthAgo = $now->modify('-30 days');

        foreach ($events as $event) {
            $endDate = (clone $event->getDateStart())->modify('+' . $event->getDuration() . ' minutes');
            $currentStatus = $event->getStatus()?->getName();
            $registeredCount = $event->getRegistred()->count();
            $maxInscription = $event->getMaxIscription();
            $deadline = $event->getDeadline();

            // 1. Historisation prioritaire
            if (
                $endDate < $oneMonthAgo &&
                $currentStatus !== 'Historisée'
            ) {
                $event->setStatus($statuses['Historisée']);
                continue;
            }

            // 2. Événement terminé
            if (
                $endDate < $now &&
                !in_array($currentStatus, ['Terminée', 'Annulée', 'Historisée'], true)
            ) {
                $event->setStatus($statuses['Terminée']);
                continue;
            }

            // 3. Évènement en cours
            if ($event->getDateStart() <= $now && $endDate > $now) {
                $event->setStatus($statuses['En cours']);
                continue;
            }

            // 4. Gestion capacité
            if ($currentStatus === 'Ouverte' && $registeredCount >= $maxInscription) {
                $event->setStatus($statuses['Clôturée']);
                continue;
            }

            if ($currentStatus === 'Clôturée' && $registeredCount < $maxInscription) {
                $event->setStatus($statuses['Ouverte']);
            }
        }

        $this->entityManager->flush();
    }

    private function getStatuses(): array
    {
        $names = [
            'Ouverte',
            'Clôturée',
            'En cours',
            'Terminée',
            'Historisée'
        ];

        $statusEntities = $this->statusRepository->findByNames($names);

        $statuses = [];
        foreach ($statusEntities as $status) {
            $statuses[$status->getName()] = $status;
        }

        // Sécurité : vérifier que tous les statuts existent
        foreach ($names as $name) {
            if (!isset($statuses[$name])) {
                throw new \LogicException(sprintf('Le statut "%s" est introuvable en base.', $name));
            }
        }

        return $statuses;
    }

}
