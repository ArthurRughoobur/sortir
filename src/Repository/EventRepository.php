<?php

namespace App\Repository;

use App\Entity\Event;
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

    function findEventList()
    {
        $qb = $this->createQueryBuilder('e');
        $qb
            ->join('e.registred', 'r')
            ->addSelect('r')
            ->join('e.organizer', 'o')
            ->addSelect('o')
            ->join('e.campus', 'c')
            ->addSelect('c')
            ->join('e.category', 'ca')
            ->addSelect('ca')
            ->join('e.status', 's')
            ->addSelect('s')
            ->andWhere('s.name = :status')
            ->setParameter('status', 'Ouverte');

        $query = $qb->getQuery();
        return $query->getResult();
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
            ->getOneOrNullResult()
            ;
    }


}
