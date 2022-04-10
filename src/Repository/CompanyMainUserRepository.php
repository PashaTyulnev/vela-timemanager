<?php

namespace App\Repository;

use App\Entity\CompanyMainUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CompanyMainUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompanyMainUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompanyMainUser[]    findAll()
 * @method CompanyMainUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompanyMainUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompanyMainUser::class);
    }


    // /**
    //  * @return CompanyMainUser[] Returns an array of CompanyMainUser objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?CompanyMainUser
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
