<?php

namespace App\DataManager\Doctrine;

use App\DataManager\ProductManagerInterface;
use App\DTO\ProductDTO;
use App\Entity\Product;
use AutoMapperPlus\AutoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProductManager implements ProductManagerInterface
{
    private $em;
    private $mapper;

    public function __construct(EntityManagerInterface $em, AutoMapperInterface $mapper)
    {
        $this->em = $em;
        $this->mapper = $mapper;
    }

    public function create(ProductDTO $productDTO): ProductDTO
    {
        $product = $this->mapper->map($productDTO, Product::class);

        $this->em->persist($product);
        $this->em->flush();
        $this->em->refresh($product);

        return $this->mapper->map($product, ProductDTO::class);
    }

    public function exists(ProductDTO $productDTO): bool
    {
        return $this->em->getRepository(Product::class)->findOneBy([
            'title' => $productDTO->title
        ]) !== null;
    }
}