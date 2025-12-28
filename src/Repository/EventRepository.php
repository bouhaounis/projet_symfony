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

    /**
     * Recherche les événements par nom, catégorie et/ou lieu
     */
    public function searchAndFilter(?string $search = null, ?int $categoryId = null, ?int $venueId = null): array
    {
        $qb = $this->createQueryBuilder('e')
                   ->leftJoin('e.venue', 'v');

        if ($search) {
            $qb->andWhere('e.nom LIKE :search OR e.description LIKE :search OR v.name LIKE :search OR v.adresse LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($categoryId) {
            $qb->andWhere('e.category = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        if ($venueId) {
            $qb->andWhere('e.venue = :venueId')
               ->setParameter('venueId', $venueId);
        }

        return $qb->orderBy('e.dateEvent', 'ASC')
                  ->getQuery()
                  ->getResult();
    }

//    /**
//     * @return Event[] Returns an array of Event objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Event
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
