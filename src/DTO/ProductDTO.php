<?php

namespace App\DTO;

use App\Validator\UniqueProductTitle;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueProductTitle()
 */
class ProductDTO extends AbstractProductDTO
{
    /**
     * @Serializer\Type("string")
     * @Serializer\ReadOnly()
     */
    public $id;

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