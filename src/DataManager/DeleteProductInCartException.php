<?php

namespace App\DataManager;

class DeleteProductInCartException extends \Exception
{
    public function __construct(array $cartDTOs)
    {
        $cartIds = [];
        foreach ($cartDTOs as $cartDTO) {
            $cartIds[] = $cartDTO->id;
        }

        $message = sprintf('Carts with the following ids contain this product at the moment', implode(", ", $cartIds));

        parent::__construct($message);
    }
}