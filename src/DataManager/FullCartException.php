<?php

namespace App\DataManager;

class FullCartException extends \Exception
{
    public function __construct($cartId, $productId)
    {
        parent::__construct(sprintf('Cannot add product "%s" to the cart. Cart "%s" is full.', $productId, $cartId));
    }
}