<?php

namespace App\Repository;

use App\Entity\OrderElement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OrderElement|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderElement|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderElement[]    findAll()
 * @method OrderElement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderElementRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OrderElement::class);
    }

    /**
     * Find active order element
     *
     * @param $parentClass
     * @param $parentId
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByParent($order, $parentClass, $parentId)
    {
        return $this->createQueryBuilder('oe')
            ->select('oe')
            ->where('oe.order = :order')
            ->andWhere('oe.parentClass = :parentClass')
            ->andWhere('oe.parentId = :parentId')
            ->setParameter('order', $order)
            ->setParameter('parentClass', $parentClass)
            ->setParameter('parentId', $parentId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    // /**
    //  * @return OrderElement[] Returns an array of OrderElement objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OrderElement
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
