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

    /**
     * Synchronise automatiquement le statut des événements en fonction de leur capacité.
     *
     * Cette méthode :
     * - Récupère les statuts "Ouverte" et "Clôturée" depuis la base de données.
     * - Ferme les événements actuellement ouverts qui ont atteint leur capacité maximale.
     * - Rouvre les événements actuellement clôturés qui sont repassés sous leur capacité maximale.
     * - Applique les changements en base de données.
     *
     * @throws \LogicException Si les statuts "Ouverte" ou "Clôturée" n'existent pas en base.
     *
     * @return void
     */
    public function syncEventStatusesWithCapacity(): void
    {
        // Récupération des statuts nécessaires
        $openStatus = $this->statusRepository->findOneBy(['name' => 'Ouverte']);
        $closedStatus = $this->statusRepository->findOneBy(['name' => 'Clôturée']);

        // Vérification de la présence des statuts en base
        if (!$openStatus || !$closedStatus) {
            throw new \LogicException('Les statuts "Ouverte" et "Clôturée" doivent exister en base.');
        }

        // Récupère les événements ouverts ayant atteint leur capacité maximale
        $eventsToClose = $this->eventRepository->findOpenEventsAtCapacity();
        foreach ($eventsToClose as $event) {
            $event->setStatus($closedStatus);
        }

        // Récupère les événements clôturés repassés sous la capacité maximale
        $eventsToReopen = $this->eventRepository->findClosedEventsBelowCapacity();
        foreach ($eventsToReopen as $event) {
            $event->setStatus($openStatus);
        }

        // Sauvegarde des modifications en base de données
        $this->entityManager->flush();
    }


}
