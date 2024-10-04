<?php

namespace App\Repository;

use App\Entity\BonDuJour;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BonDuJour>
 */
class BonDuJourRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BonDuJour::class);
    }

    //    /**
    //     * @return BonDuJour[] Returns an array of BonDuJour objects
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

    //    public function findOneBySomeField($value): ?BonDuJour
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    // Requête pour obtenir un bon spécifique à une date et un utilisateur
    public function findOneByDateAndUser(\DateTimeInterface $date, string $user): ?Bon
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.date = :date')
            ->andWhere('b.createdBy = :user')
            ->setParameter('date', $date)
            ->setParameter('user', $user)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
