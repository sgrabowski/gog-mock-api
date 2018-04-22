<?php

namespace App\DataManager;

use App\DTO\AbstractProductDTO;
use App\DTO\PriceDTO;
use App\DTO\ProductDTO;
use App\DTO\UpdateProductDTO;

interface ProductManagerInterface
{
    /**
     * Creates a new product
     *
     * @param ProductDTO $productDTO
     * @return ProductDTO
     */
    public function create(ProductDTO $productDTO): ProductDTO;

    /**
     * Checks if the product already exists
     *
     * @param AbstractProductDTO $productDTO
     * @return bool
     */
    public function exists(AbstractProductDTO $productDTO): bool;

    /**
     * Updates the product
     * Only non-null values are taken into account when updating object
     *
     * @param $id mixed product id
     * @param UpdateProductDTO $updateProductDTO
     * @return ProductDTO
     * @throws ObjectNotFoundException
     */
    public function update($id, UpdateProductDTO $updateProductDTO): ProductDTO;

    /**
     * Replaces the product
     *
     * @param $id mixed product id
     * @param ProductDTO $productDTO
     * @return ProductDTO
     * @throws ObjectNotFoundException
     */
    public function replace($id, ProductDTO $productDTO): ProductDTO;

    /**
     * Creates or updates a price
     *
     * @param $id mixed product id
     * @param PriceDTO $priceDTO
     * @return ProductDTO
     * @throws ObjectNotFoundException
     */
    public function setPrice($id, PriceDTO $priceDTO): ProductDTO;
}