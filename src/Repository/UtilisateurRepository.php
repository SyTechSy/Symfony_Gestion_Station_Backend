<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    // Creation de l'utilisateur par son email
    /*public function findOneByEmailUtilisateur($email): ?Utilisateur
    {
        return $this->createQueryBuilder('utilisateur')
            ->where('utilisateur.emailUtilisateur = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();
    }*/

    public function findByEmailUtilisateurAndMotDePasse(string $email, string $motDePasse): ?Utilisateur
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.emailUtilisateur = :email')
            ->andWhere('u.motDePasse = :motDePasse')
            ->setParameter('email', $email)
            ->setParameter('motDePasse', $motDePasse)
            ->getQuery()
            ->getOneOrNullResult();
    }




    //    /**
    //     * @return Utilisateur[] Returns an array of Utilisateur objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Utilisateur
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
