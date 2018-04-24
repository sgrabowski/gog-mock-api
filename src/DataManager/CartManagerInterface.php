<?php

namespace App\DataManager;

use App\DTO\CartDTO;
use App\DTO\CurrencyDTO;

interface CartManagerInterface
{
    /**
     * Creates an empty cart with unique id
     * A single cart can handle only products in one currency
     *
     * @param CurrencyDTO $currencyDTO
     * @return CartDTO
     */
    public function create(CurrencyDTO $currencyDTO): CartDTO;

    /**
     * Updates the cart to contain exactly $quantity of $productId items
     * Therefore even if the product is already in the cart, the quantity will be updated (*but not added*)
     *
     * @param $cartId
     * @param $productId
     * @param $quantity
     * @return CartDTO
     */
    public function setProduct($cartId, $productId, $quantity): CartDTO;

    /**
     * Returns cart's total amount
     *
     * @param CartDTO $cartDTO
     * @return CartDTO
     */
    public function getCartTotal(CartDTO $cartDTO);

    /**
     * Finds a cart by id
     *
     * @param $id
     * @return CartDTO
     */
    public function find($id): CartDTO;
}