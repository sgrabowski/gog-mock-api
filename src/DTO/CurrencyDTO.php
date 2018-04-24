<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class CurrencyDTO
{
    /**
     * @Serializer\Type("string")
     * @Assert\NotBlank(message="Currency code is required")
     * @Assert\Currency
     */
    public $currency;

}