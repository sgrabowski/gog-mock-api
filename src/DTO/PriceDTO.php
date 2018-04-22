<?php

namespace App\DTO;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class PriceDTO
{
    /**
     * @Serializer\Type("string")
     * @Assert\NotBlank(message="Currency code is required")
     * @Assert\Currency
     */
    public $currency;

    /**
     * @Serializer\Type("string")
     * @Assert\NotBlank(message="Amount is required")
     * @Assert\GreaterThanOrEqual(value=0)
     */
    public $amount;
}