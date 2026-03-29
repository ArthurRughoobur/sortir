<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use App\Form\Model\EventSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findEventList(EventSearch $eventSearch, User $user): array
    {
        $qb = $this->createQueryBuilder('e')
            ->distinct()
            ->leftJoin('e.registred', 'r')
            ->addSelect('r')
            ->leftJoin('e.organizer', 'o')
            ->addSelect('o')
            ->leftJoin('e.campus', 'c')
            ->addSelect('c')
            ->leftJoin('e.category', 'ca')
            ->addSelect('ca')
            ->leftJoin('e.status', 's')
            ->addSelect('s');

        if ($eventSearch->getTerminee()) {
            $qb->andWhere('s.name = :status')
                ->setParameter('status', 'Terminée');
        } else {
            $qb->andWhere('s.name IN (:statuses)')
                ->setParameter('statuses', ['Ouverte', 'En cours', 'Clôturée']);
        }

        if ($eventSearch->getCampus()) {
            $qb->andWhere('e.campus = :campus')
                ->setParameter('campus', $eventSearch->getCampus());
        }

        if ($eventSearch->getCategory()) {
            $qb->andWhere('e.category = :category')
                ->setParameter('category', $eventSearch->getCategory());
        }

        if ($eventSearch->getName()) {
            $qb->andWhere('e.name LIKE :name')
                ->setParameter('name', '%' . $eventSearch->getName() . '%');
        }

        if ($eventSearch->getDateStart()) {
            $qb->andWhere('e.dateStart >= :dateStart')
                ->setParameter('dateStart', $eventSearch->getDateStart());
        }

        if ($eventSearch->getDeadline()) {
            $qb->andWhere('e.dateStart <= :deadline')
                ->setParameter('deadline', $eventSearch->getDeadline());
        }

        if ($user) {
            $isOrganizer = $eventSearch->getOrganizer();
            $isRegistered = $eventSearch->getRegistered();
            $isNotRegistered = $eventSearch->getNotRegistered();

            $conditions = [];

            if ($isOrganizer) {
                $conditions[] = 'e.organizer = :user';
            }

            if ($isRegistered) {
                $conditions[] = ':user MEMBER OF e.registred';
            }

            if ($isNotRegistered) {
                $conditions[] = ':user NOT MEMBER OF e.registred';
            }

            if (!empty($conditions)) {
                $qb->andWhere('(' . implode(' OR ', $conditions) . ')')
                    ->setParameter('user', $user);
            }
        }

        return $qb->orderBy('e.dateStart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findEventById($id): ?Event
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.registred', 'r')
            ->addSelect('r')
            ->leftJoin('e.organizer', 'o')
            ->addSelect('o')
            ->leftJoin('e.campus', 'c')
            ->addSelect('c')
            ->leftJoin('e.status', 's')
            ->addSelect('s')
            ->leftJoin('e.category', 'ca')
            ->addSelect('ca')
            ->leftJoin('e.adress', 'a')
            ->addSelect('a')
            ->leftjoin('a.city', 'ci')
            ->addSelect('ci')
            ->andWhere('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findEventWithEndDate() {

        return $this->createQueryBuilder('event')
            ->join('event.status', 'status')
            ->where('DATE_ADD(event.dateStart, event.duration, \'minute\') < :now')
            ->andWhere('status.name NOT IN (:excludedStatusNames)')
            ->setParameter('now', new \DateTime())
            ->setParameter('excludedStatusNames', ['Terminée', 'Annulée', 'Historisée'])
            ->getQuery()
            ->getResult();
    }

    public function findOpenEventsAtCapacity(): array
    {
        return $this->createQueryBuilder('event')
            ->join('event.status', 'status')
            ->leftJoin('event.registred', 'registred')
            ->where('status.name = :status')
            ->setParameter('status', 'Ouverte')
            ->groupBy('event.id')
            ->having('COUNT(registred) >= event.maxIscription')
            ->getQuery()
            ->getResult();
    }

    public function findClosedEventsBelowCapacity(): array
    {
        return $this->createQueryBuilder('event')
            ->join('event.status', 'status')
            ->leftJoin('event.registred', 'registred')
            ->where('status.name = :status')
            ->setParameter('status', 'Clôturée')
            ->groupBy('event.id')
            ->having('COUNT(registred) < event.maxIscription')
            ->getQuery()
            ->getResult();
    }


}
