<?php

namespace App\Repository;

use App\Entity\TimeEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TimeEntry|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimeEntry|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimeEntry[]    findAll()
 * @method TimeEntry[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimeEntryRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, TimeEntry::class);
        $this->entityManager = $entityManager;
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(TimeEntry $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(TimeEntry $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return TimeEntry[] Returns an array of TimeEntry objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TimeEntry
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    /**
     * @throws NonUniqueResultException
     */
    public function findByDate($objectId, $from, $to)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.object = :id')
            ->setParameter('id', $objectId)
            ->andWhere('t.createdAt >= :from')
            ->setParameter('from', $from)
            ->andWhere('t.createdAt <= :to')
            ->setParameter('to', $to)
            ->getQuery()
            ->getResult();
    }

    public function findMinDate($object)
    {
       $query=$this->entityManager->createQuery('SELECT MIN(t.createdAt) FROM App\Entity\TimeEntry t WHERE t.object='.$object);
       return $query->getResult();
    }

    public function findMaxDate($object)
    {
        $query=$this->entityManager->createQuery('SELECT MAX(t.createdAt) FROM App\Entity\TimeEntry t WHERE t.object='.$object);
        return $query->getResult();
    }

}
