<?php

namespace App\Repository;

use App\Entity\MovementType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method MovementType|null find($id, $lockMode = null, $lockVersion = null)
 * @method MovementType|null findOneBy(array $criteria, array $orderBy = null)
 * @method MovementType[]    findAll()
 * @method MovementType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovementTypeRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, MovementType::class);
    }

    // /**
    //  * @return MovementType[] Returns an array of MovementType objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MovementType
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
