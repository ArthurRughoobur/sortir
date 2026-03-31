<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\User;
use App\Form\Model\EventSearch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository dédié à la gestion des entités Event.
 *
 * Cette classe centralise les requêtes personnalisées liées aux événements,
 * notamment :
 * - la récupération d'une liste filtrée d'événements
 * - la récupération détaillée d'un événement par son identifiant
 * - la recherche des événements terminés ou à historiser
 * - la détection des événements complets ou à rouvrir selon leur capacité
 *
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    /**
     * Initialise le repository avec le registre Doctrine.
     *
     * @param ManagerRegistry $registry Registre des managers Doctrine
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * Récupère la liste des événements selon les critères de recherche fournis.
     *
     * Les filtres possibles sont :
     * - le statut de l'événement
     * - le campus
     * - la catégorie
     * - le nom
     * - la date de début minimale
     * - la date limite maximale
     * - le lien entre l'utilisateur et l'événement :
     *   - organisateur
     *   - inscrit
     *   - non inscrit
     *
     * Si l'option "terminée" est activée, seuls les événements terminés sont renvoyés.
     * Sinon, seuls les événements avec les statuts "Ouverte", "En cours" et "Clôturée"
     * sont renvoyés.
     *
     * @param EventSearch $eventSearch Objet contenant les critères de recherche
     * @param User $user Utilisateur connecté utilisé pour certains filtres
     *
     * @return Event[] Liste des événements correspondant aux critères
     */
    public function findEventList(EventSearch $eventSearch, User $user): array
    {
        // Initialise le QueryBuilder principal sur l'entité Event
        $qb = $this->createQueryBuilder('e')
            ->distinct()
            // Charge les relations utiles pour éviter certains accès supplémentaires en base
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

        // Filtre sur le statut des événements :
        // soit uniquement les événements terminés,
        // soit les événements encore "actifs" ou consultables
        if ($eventSearch->getTerminee()) {
            $qb->andWhere('s.name = :status')
                ->setParameter('status', 'Terminée');
        } else {
            $qb->andWhere('s.name IN (:statuses)')
                ->setParameter('statuses', ['En création','Ouverte', 'En cours', 'Clôturée']);
        }

        // Filtre sur le campus sélectionné
        if ($eventSearch->getCampus() !== null) {
            $qb->andWhere('e.campus = :campus')
                ->setParameter('campus', $eventSearch->getCampus());
        }

        // Filtre sur la catégorie sélectionnée
        if ($eventSearch->getCategory()) {
            $qb->andWhere('e.category = :category')
                ->setParameter('category', $eventSearch->getCategory());
        }

        // Filtre sur le nom de l'événement avec recherche partielle
        if ($eventSearch->getName()) {
            $qb->andWhere('e.name LIKE :name')
                ->setParameter('name', '%' . $eventSearch->getName() . '%');
        }

        // Filtre sur la date minimale de début
        if ($eventSearch->getDateStart()) {
            $qb->andWhere('e.dateStart >= :dateStart')
                ->setParameter('dateStart', $eventSearch->getDateStart());
        }

        // Filtre sur la date maximale de début
        // Attention : ici le nom "deadline" côté paramètre peut prêter à confusion
        // car on compare en réalité e.dateStart
        if ($eventSearch->getDeadline()) {
            $qb->andWhere('e.dateStart <= :deadline')
                ->setParameter('deadline', $eventSearch->getDeadline());
        }

        // Ajoute les filtres liés à l'utilisateur connecté
        if ($user) {
            $isOrganizer = $eventSearch->getOrganizer();
            $isRegistered = $eventSearch->getRegistered();
            $isNotRegistered = $eventSearch->getNotRegistered();

            // On stocke les conditions pour les combiner ensuite avec OR
            $conditions = [];

            // L'utilisateur est organisateur de l'événement
            if ($isOrganizer) {
                $conditions[] = 'e.organizer = :user';
            }

            // L'utilisateur est inscrit à l'événement
            if ($isRegistered) {
                $conditions[] = ':user MEMBER OF e.registred';
            }

            // L'utilisateur n'est pas inscrit à l'événement
            if ($isNotRegistered) {
                $conditions[] = ':user NOT MEMBER OF e.registred';
            }

            // Si au moins un filtre utilisateur est actif,
            // on regroupe les conditions dans une clause OR
            if (!empty($conditions)) {
                $qb->andWhere('(' . implode(' OR ', $conditions) . ')')
                    ->setParameter('user', $user);
            }
        }

        // Trie les événements par date de début croissante
        return $qb->orderBy('e.dateStart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère un événement par son identifiant avec ses relations principales.
     *
     * Cette méthode charge également :
     * - les inscrits
     * - l'organisateur
     * - le campus
     * - le statut
     * - la catégorie
     * - l'adresse
     * - la ville liée à l'adresse
     *
     * @param int $id Identifiant de l'événement
     *
     * @return Event|null L'événement trouvé ou null s'il n'existe pas
     */
    public function findEventById(int $id): ?Event
    {
        return $this->createQueryBuilder('e')
            // Charge les relations nécessaires pour avoir un événement détaillé
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
            ->leftJoin('a.city', 'ci')
            ->addSelect('ci')
            // Filtre sur l'identifiant de l'événement demandé
            ->andWhere('e.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Récupère les événements dont la date de fin est dépassée.
     *
     * La date de fin est calculée à partir de :
     * - la date de début
     * - la durée de l'événement en minutes
     *
     * Les événements ayant déjà les statuts "Terminée", "Annulée"
     * ou "Historisée" sont exclus.
     *
     * @return Event[] Liste des événements arrivés à échéance
     */
    public function findEventWithEndDate(): array
    {
        return $this->createQueryBuilder('event')
            ->join('event.status', 'status')
            // Calcule la fin de l'événement avec DATE_ADD(dateStart, duration, 'minute')
            ->where('DATE_ADD(event.dateStart, event.duration, \'minute\') < :now')
            // Exclut les événements déjà traités côté workflow
            ->andWhere('status.name NOT IN (:excludedStatusNames)')
            ->setParameter('now', new \DateTime())
            ->setParameter('excludedStatusNames', ['Terminée', 'Annulée', 'Historisée'])
            ->getQuery()
            ->getResult();
    }

    public function findAllForStatusUpdate(): array
    {
        return $this->createQueryBuilder('event')
            ->join('event.status', 'status')
            ->addSelect('status')
            ->leftJoin('event.registred', 'registred')
            ->addSelect('registred')
            ->getQuery()
            ->getResult();
    }

}
