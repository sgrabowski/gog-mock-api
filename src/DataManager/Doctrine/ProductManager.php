<?php

namespace App\DataManager\Doctrine;

use App\DataManager\CartManagerInterface;
use App\DataManager\DeleteProductInCartException;
use App\DataManager\ObjectNotFoundException;
use App\DataManager\ProductManagerInterface;
use App\DataManager\ProductNotFoundException;
use App\DTO\AbstractProductDTO;
use App\DTO\PriceDTO;
use App\DTO\ProductDTO;
use App\DTO\UpdateProductDTO;
use App\Entity\Price;
use App\Entity\Product;
use App\Pagination\Doctrine\DoctrineProductPaginator;
use App\Pagination\PaginatorInterface;
use AutoMapperPlus\AutoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;

class ProductManager implements ProductManagerInterface
{
    private $em;
    private $mapper;
    private $repo;
    private $cartManager;

    const REMOVE_IN_CART_FORBID = 0;
    const REMOVE_IN_CART_FORCE = 1;
    private $removeIfExistsInCartLogic;

    public function __construct(EntityManagerInterface $em, AutoMapperInterface $mapper)
    {
        $this->em = $em;
        $this->mapper = $mapper;
        $this->repo = $this->em->getRepository(Product::class);
        $this->removeIfExistsInCartLogic = self::REMOVE_IN_CART_FORBID;
    }

    /**
     * @required
     * This can't be set in the constructor to avoid circular reference
     */
    public function setCartManager(CartManagerInterface $cartManager)
    {
        $this->cartManager = $cartManager;
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
        return $this->repo->findOneByDTO($productDTO) !== null;
    }

    public function update($id, UpdateProductDTO $updateProductDTO): ProductDTO
    {
        $product = $this->repo->find($id);

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
        $product = $this->repo->find($id);

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
        $product = $this->repo->find($id);

        if ($product === null) {
            throw new ObjectNotFoundException();
        }

        $price = $this->mapper->map($priceDTO, Price::class);

        $product->replacePrice($price);
        $this->em->flush();
        $this->em->refresh($product);

        return $this->mapper->map($product, ProductDTO::class);
    }

    /**
     * Creates a paginator for product list
     *
     * @return PaginatorInterface
     * @throws \App\Pagination\PageNotFoundException
     */
    public function getPaginator(int $page, int $limit): PaginatorInterface
    {
        $paginator = new DoctrineProductPaginator($this->repo, $this->mapper);
        $paginator->setMaxPerPage($limit);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    /**
     * Finds a product by it's id
     *
     * @param $id
     * @return ProductDTO
     * @throws ProductNotFoundException
     */
    public function find($id, $currency): ProductDTO
    {
        $product = $this->repo->findOneWithCurrency($id, $currency);

        if (!$product) {
            throw new ProductNotFoundException();
        }

        return $this->mapper->map($product, ProductDTO::class);
    }

    /**
     * Finds multiple products
     *
     * @param array $ids
     * @return array|ProductDTO[]
     * @throws ProductNotFoundException
     */
    public function findMultiple(array $ids, $currency)
    {
        $results = [];
        $products = $this->repo->findWithCurrency($ids, $currency);

        if (count($products) != count($ids)) {
            //@todo: throw exception stating which products were not found
        }

        foreach ($products as $product) {
            $results[] = $this->mapper->map($product, ProductDTO::class);
        }

        return $results;
    }

    /**
     * Removes a product
     *
     * @param $cartId
     * @param $productId
     * @return bool True if succesful
     */
    public function remove($id): bool
    {
        $product = $this->repo->find($id);

        if ($product === null) {
            throw new ObjectNotFoundException();
        }

        $cartsWithThisProduct = $this->cartManager->getCartsContainingProducts([$id]);

        if(count($cartsWithThisProduct) > 0) {
            switch ($this->removeIfExistsInCartLogic) {
                case self::REMOVE_IN_CART_FORBID:
                    throw new DeleteProductInCartException($cartsWithThisProduct);
                    break;
                case self::REMOVE_IN_CART_FORCE:
                    $this->removeProductFromCarts($cartsWithThisProduct, $id);
                    break;
                default:
                    return false;
            }
        }

        //no carts hold this product, we can safely remove it

        $this->em->remove($product);
        $this->em->flush();

        return true;
    }

    //@todo: Performance issue - do this in a single call instead of a loop
    protected function removeProductFromCarts($cartDTOs, $productId) {
        foreach ($cartDTOs as $cartDTO) {
            $this->cartManager->removeProduct($cartDTO->id, $productId);
        }
    }
}