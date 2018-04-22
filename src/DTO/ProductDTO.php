<?php

namespace App\DTO;

use App\Validator\UniqueProductTitle;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueProductTitle()
 */
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