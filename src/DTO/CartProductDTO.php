<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;

class CartProductDTO
{
    /**
     * @Serializer\Type("App\DTO\ProductDTO")
     */
    public $product;

    /**
     * @Serializer\Type("integer")
     */
    public $quantity;
}