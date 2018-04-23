<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;

class CartDTO
{
    /**
     * @Serializer\Type("string")
     */
    public $id;

    /**
     * @@Serializer\Type("array<App\DTO\CartProductDTO>")
     */
    public $products;

    /**
     * @Serializer\Type("string")
     */
    public $currency;

    /**
     * @Serializer\Type("string")
     */
    public $total;
}