<?php

namespace App\Repository;

use App\DTO\AbstractProductDTO;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function getListQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder("p")
            ->join("p.prices", "pr");
    }

    public function findOneByDTO(AbstractProductDTO $productDTO)
    {
        return $this->createQueryBuilder("p")
            ->where("p.title = :title")
            ->orWhere("p.id = :id")
            ->setParameters([
                "title" => $productDTO->title,
                "id" => $productDTO->id
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findOneWithCurrency($id, $currency)
    {
        return $this->createQueryBuilder("p")
            ->join("p.prices", "pr")
            ->where("pr.currency = :currency")
            ->andWhere("p.id = :id")
            ->setParameters([
                "id" => $id,
                "currency" => $currency
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findWithCurrency(array $ids, $currency)
    {
        return $this->createQueryBuilder("p")
            ->join("p.prices", "pr")
            ->where("pr.currency = :currency")
            ->andWhere("p.id IN (:ids)")
            ->setParameters([
                "ids" => $ids,
                "currency" => $currency
            ])
            ->getQuery()
            ->getResult();
    }
}
