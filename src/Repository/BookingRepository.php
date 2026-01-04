<?php

namespace App\Repository;

use App\Entity\Booking;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Booking>
 */
class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    /**
     * Find all bookings for a specific user
     *
     * @param User $user
     * @return Booking[]
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.user = :user')
            ->setParameter('user', $user)
            ->orderBy('b.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find active (non-cancelled) bookings for a specific user
     *
     * @param User $user
     * @return Booking[]
     */
    public function findActiveByUser(User $user): array
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.user = :user')
            ->andWhere('b.status != :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'cancelled')
            ->orderBy('b.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Créer un QueryBuilder avec filtres avancés pour les bookings
     */
    public function createQueryBuilderWithFilters(
        User $user,
        ?string $status = null,
        ?\DateTimeInterface $dateFrom = null,
        ?\DateTimeInterface $dateTo = null,
        ?float $totalMin = null,
        ?float $totalMax = null,
        ?int $eventId = null
    ) {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.event', 'e')
            ->where('b.user = :user')
            ->setParameter('user', $user);

        if ($status) {
            $qb->andWhere('b.status = :status')
               ->setParameter('status', $status);
        }

        if ($dateFrom) {
            $qb->andWhere('b.createdAt >= :dateFrom')
               ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo) {
            $qb->andWhere('b.createdAt <= :dateTo')
               ->setParameter('dateTo', $dateTo);
        }

        if ($totalMin !== null) {
            $qb->andWhere('b.total >= :totalMin')
               ->setParameter('totalMin', $totalMin);
        }

        if ($totalMax !== null) {
            $qb->andWhere('b.total <= :totalMax')
               ->setParameter('totalMax', $totalMax);
        }

        if ($eventId) {
            $qb->andWhere('b.event = :eventId')
               ->setParameter('eventId', $eventId);
        }

        return $qb->orderBy('b.createdAt', 'DESC');
    }

//    /**
//     * @return Booking[] Returns an array of Booking objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Booking
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
