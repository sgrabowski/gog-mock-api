<?php

namespace App\DataManager\Doctrine;

use App\DataManager\CartManagerInterface;
use App\DTO\CartDTO;
use App\DTO\CurrencyDTO;
use App\Entity\Cart;
use AutoMapperPlus\AutoMapperInterface;
use Doctrine\ORM\EntityManagerInterface;

class CartManager implements CartManagerInterface
{
    private $em;
    private $mapper;
    private $repo;

    public function __construct(EntityManagerInterface $em, AutoMapperInterface $mapper)
    {
        $this->em = $em;
        $this->mapper = $mapper;
        $this->repo = $this->em->getRepository(Cart::class);
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
}