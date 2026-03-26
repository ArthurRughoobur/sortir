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
                ->setParameter('statuses', ['Ouverte', 'En cours']);
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

//        if ($eventSearch->getTerminee()) {
//            $qb->andWhere('s.name = :status')
//                ->setParameter('status', 'Terminée');
//        }

        if ($user) {
            if ($eventSearch->getOrganizer()) {
                $qb->andWhere('e.organizer = :user')
                    ->setParameter('user', $user);
            }

            if ($eventSearch->getRegistered()) {
                $qb->andWhere(':user MEMBER OF e.registred')
                    ->setParameter('user', $user);
            }

            if ($eventSearch->getNotRegistered()) {
                $qb->andWhere(':user NOT MEMBER OF e.registred')
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


}
