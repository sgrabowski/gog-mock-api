<?php

namespace App\DTO;

use App\Validator\UniqueProductTitle;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueProductTitle()
 */
class UpdateProductDTO extends AbstractProductDTO
{
    /**
     * @Serializer\Type("string")
     */
    public $title;
}