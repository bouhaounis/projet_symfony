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
     * @deprecated Use createQueryBuilderWithFilters() instead
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

    /**
     * Créer un QueryBuilder avec tous les filtres disponibles
     */
    public function createQueryBuilderWithFilters(
        ?string $search = null,
        ?int $categoryId = null,
        ?int $venueId = null,
        ?\DateTimeInterface $dateFrom = null,
        ?\DateTimeInterface $dateTo = null,
        ?float $priceMin = null,
        ?float $priceMax = null
    ) {
        $qb = $this->createQueryBuilder('e')
            ->leftJoin('e.venue', 'v')
            ->leftJoin('e.category', 'c');

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

        if ($dateFrom) {
            $qb->andWhere('e.dateEvent >= :dateFrom')
               ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo) {
            $qb->andWhere('e.dateEvent <= :dateTo')
               ->setParameter('dateTo', $dateTo);
        }

        if ($priceMin !== null) {
            $qb->andWhere('e.prix >= :priceMin')
               ->setParameter('priceMin', $priceMin);
        }

        if ($priceMax !== null) {
            $qb->andWhere('e.prix <= :priceMax')
               ->setParameter('priceMax', $priceMax);
        }

        return $qb->orderBy('e.dateEvent', 'ASC');
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
