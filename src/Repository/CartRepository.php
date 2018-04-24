<?php

namespace App\Repository;

use App\Entity\Cart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Cart|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cart|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cart[]    findAll()
 * @method Cart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CartRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    public function findByProductIds(array $productIds)
    {
        return $this->createQueryBuilder("p")
            ->join("p.products", "cp")
            ->where("cp.productId IN (:productIds)")
            ->setParameter("productIds", $productIds)
            ->getQuery()
            ->getResult();
    }
}
