<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;

class CartDTO
{
    /**
     * @Serializer\Type("string")
     * @Serializer\ReadOnly()
     */
    public $id;
}