<?php

namespace App\DataManager;

use App\DTO\ProductDTO;

interface ProductManagerInterface
{
    public function create(ProductDTO $productDTO): ProductDTO;
}