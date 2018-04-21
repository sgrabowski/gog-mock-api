<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class ProductDTO
{
    /**
     * @Serializer\Type("string")
     * @Assert\NotBlank(message="Title is required")
     */
    public $title;

    /**
     * @Serializer\Type("array<App\DTO\PriceDTO>")
     * @Assert\Valid()
     */
    public $prices;
}