<?php

namespace App\Service;

use App\Repository\EventRepository;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;

class UpdateEventStatus
{

    public function __construct(
        private EventRepository        $eventRepository,
        private StatusRepository       $statusRepository,
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function updatePastEvent(): void
    {
        $terminatedStatus = $this->statusRepository->findOneBy(['name' => 'Terminée']);
        $historicizedStatus = $this->statusRepository->findOneBy(['name'=> 'Historisée']);
        $finishedEvents = $this->eventRepository->findEventWithEndDate();
        $historizedEvents = $this->eventRepository->finishedToHistorized();

        foreach ($finishedEvents as $event) {
            $event->setStatus($terminatedStatus);
            $this->entityManager->persist($event);
        }
        foreach ($historizedEvents as $event) {
            $event->setStatus($historicizedStatus);
            $this->entityManager->persist($event);
        }
      
        $this->entityManager->flush();
    }

    public function syncEventStatusesWithCapacity(): void
    {
        $openStatus = $this->statusRepository->findOneBy(['name' => 'Ouverte']);
        $closedStatus = $this->statusRepository->findOneBy(['name' => 'Clôturée']);

        if (!$openStatus || !$closedStatus) {
            throw new \LogicException('Les statuts "Ouverte" et "Clôturée" doivent exister en base.');
        }

        $eventsToClose = $this->eventRepository->findOpenEventsAtCapacity();
        foreach ($eventsToClose as $event) {
            $event->setStatus($closedStatus);
        }

        $eventsToReopen = $this->eventRepository->findClosedEventsBelowCapacity();
        foreach ($eventsToReopen as $event) {
            $event->setStatus($openStatus);
        }

        $this->entityManager->flush();
    }


}
