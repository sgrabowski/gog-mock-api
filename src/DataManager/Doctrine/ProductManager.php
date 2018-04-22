<?php

namespace App\DataManager\Doctrine;

use App\DataManager\ObjectNotFoundException;
use App\DataManager\ProductManagerInterface;
use App\DTO\AbstractProductDTO;
use App\DTO\PriceDTO;
use App\DTO\ProductDTO;
use App\DTO\UpdateProductDTO;
use App\Entity\Price;
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

    public function exists(AbstractProductDTO $productDTO): bool
    {
        return $this->em->getRepository(Product::class)->findOneBy([
                'title' => $productDTO->title
            ]) !== null;
    }

    public function update($id, UpdateProductDTO $updateProductDTO): ProductDTO
    {
        $product = $this->em->getRepository(Product::class)->find($id);

        if ($product === null) {
            throw new ObjectNotFoundException();
        }

        if (!empty($updateProductDTO->title)) {
            $product->setTitle($updateProductDTO->title);
        }
        $this->em->flush();
        $this->em->refresh($product);

        return $this->mapper->map($product, ProductDTO::class);
    }

    public function replace($id, ProductDTO $productDTO): ProductDTO
    {
        /* @var $product Product */
        $product = $this->em->getRepository(Product::class)->find($id);

        if ($product === null) {
            throw new ObjectNotFoundException();
        }

        $new = $this->mapper->map($productDTO, Product::class);

        $product->setTitle($new->getTitle());
        $product->getPrices()->clear();

        foreach ($new->getPrices() as $price) {
            $product->addPrice($price);
        }

        $this->em->flush();
        $this->em->refresh($product);

        return $this->mapper->map($product, ProductDTO::class);
    }

    public function setPrice($id, PriceDTO $priceDTO): ProductDTO
    {
        $product = $this->em->getRepository(Product::class)->find($id);

        if ($product === null) {
            throw new ObjectNotFoundException();
        }

        $price = $this->mapper->map($priceDTO, Price::class);

        $product->replacePrice($price);
        $this->em->flush();
        $this->em->refresh($product);

        return $this->mapper->map($product, ProductDTO::class);
    }
}