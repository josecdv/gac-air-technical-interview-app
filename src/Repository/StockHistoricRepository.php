<?php

namespace App\Repository;

use App\Entity\StockHistoric;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StockHistoric>
 *
 * @method StockHistoric|null find($id, $lockMode = null, $lockVersion = null)
 * @method StockHistoric|null findOneBy(array $criteria, array $orderBy = null)
 * @method StockHistoric[]    findAll()
 * @method StockHistoric[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockHistoricRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StockHistoric::class);
    }

    /**
     * @return StockHistoric[] Returns an array of StockHistoric objects
     */
    public function findByProduct($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.product_id = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();
    }


    /*
    public function findOneBySomeField($value): ?StockHistoric
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
