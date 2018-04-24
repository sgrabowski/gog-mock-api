<?php

namespace App\DataManager;

class ProductNotFoundException extends ObjectNotFoundException
{
    public function __construct($message = "Product not found", $code = 400)
    {
        parent::__construct($message, $code);
    }
}