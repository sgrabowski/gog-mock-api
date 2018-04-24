<?php

namespace App\DataManager\Doctrine;

use App\DataManager\CartManagerInterface;
use App\DataManager\FullCartException;
use App\DataManager\ObjectNotFoundException;
use App\DataManager\ProductManagerInterface;
use App\DataManager\ProductNotFoundException;
use App\DTO\CartDTO;
use App\DTO\CurrencyDTO;
use App\DTO\ProductDTO;
use App\Entity\Cart;
use App\Entity\CartProduct;
use AutoMapperPlus\AutoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\DecimalMoneyFormatter;
use Money\Money;
use Money\Parser\DecimalMoneyParser;

class CartManager implements CartManagerInterface
{
    const ITEMS_LIMIT = 3;
    private $em;
    private $mapper;
    private $repo;
    private $productManager;

    public function __construct(EntityManagerInterface $em, AutoMapperInterface $mapper, ProductManagerInterface $productManager)
    {
        $this->em = $em;
        $this->mapper = $mapper;
        $this->repo = $this->em->getRepository(Cart::class);
        $this->productManager = $productManager;
    }

    /**
     * Creates an empty cart with unique id
     * A single cart can handle only products in one currency
     *
     * @return CartDTO
     */
    public function create(CurrencyDTO $currencyDTO): CartDTO
    {
        $cart = new Cart();
        $cart->setCurrency($currencyDTO->currency);

        $this->em->persist($cart);
        $this->em->flush();

        return $this->mapper->map($cart, CartDTO::class);
    }

    /**
     * Updates the cart to contain exactly $quantity of $productId items
     * Therefore even if the product is already in the cart, the quantity will be updated (*but not added*)
     *
     * @param $cartId
     * @param $productId
     * @param $quantity
     * @return CartDTO
     */
    public function setProduct($cartId, $productId, $quantity): CartDTO
    {
        /* @var $cart Cart */
        $cart = $this->repo->find($cartId);

        if ($cart === null) {
            throw new ObjectNotFoundException(sprintf("Cart with id %s doesn't exist", $cartId));
        }

        //@todo: move this check out to an abstract class
        if ($cart->getProducts()->count() >= self::ITEMS_LIMIT) {
            throw new FullCartException($cartId, $productId);
        }

        $productDTO = new ProductDTO();
        $productDTO->id = $productId;

        if (!$this->productManager->exists($productDTO)) {
            throw new ProductNotFoundException(sprintf("Product with id %s doesn't exist", $productId));
        }

        $cartProduct = new CartProduct();
        $cartProduct->setProductId($productId);
        $cartProduct->setQuantity($quantity);

        $cart->updateProduct($cartProduct);

        $this->em->persist($cart);
        $this->em->flush();
        $this->em->refresh($cart);

        return $this->mapper->map($cart, CartDTO::class);
    }

    /**
     * Returns cart's total amount
     *
     * @param CartDTO $cartDTO
     * @return CartDTO
     */
    public function getCartTotal(CartDTO $cartDTO)
    {
        $currency = new Currency($cartDTO->currency);
        $total = new Money(0, $currency);
        $currencies = new ISOCurrencies();
        $moneyParser = new DecimalMoneyParser($currencies);
        $moneyFormatter = new DecimalMoneyFormatter($currencies);

        foreach ($cartDTO->products as $cartProductDTO) {
            $productDTO = $cartProductDTO->product;

            if (empty($productDTO->prices) || $productDTO->prices[0]->currency != $cartDTO->currency) {
                throw new \LogicException(sprintf("Cart %s contains products with either no price or price in different currency than cart's currency", $cartDTO->id));
            }

            $singleProductPrice = $moneyParser->parse($productDTO->prices[0]->amount, $currency);
            $subtotal = $singleProductPrice->multiply($cartProductDTO->quantity);
            $total = $total->add($subtotal);
        }

        return $moneyFormatter->format($total);
    }

    /**
     * Finds a cart by id
     *
     * @param $id
     * @return CartDTO
     * @throws ObjectNotFoundException
     */
    public function find($id): CartDTO
    {
        /* @var $cart Cart */
        $cart = $this->repo->find($id);

        if ($cart === null) {
            throw new ObjectNotFoundException(sprintf("Cart with id %s doesn't exist", $cartId));
        }

        return $this->mapper->map($cart, CartDTO::class);
    }
}