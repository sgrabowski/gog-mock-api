<?php

namespace App\DataManager;

use App\DTO\CartDTO;
use App\DTO\CurrencyDTO;

interface CartManagerInterface
{
    /**
     * Creates an empty cart with unique id
     * A single cart can handle only products in one currency
     *
     * @return CartDTO
     */
    public function create(CurrencyDTO $currencyDTO): CartDTO;
}