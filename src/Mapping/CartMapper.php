<?php

namespace App\Mapping;

use App\DataManager\CartManagerInterface;
use App\DataManager\ProductManagerInterface;
use App\DTO\CartDTO;
use App\DTO\CartProductDTO;
use App\DTO\ProductDTO;
use App\Entity\Cart;
use AutoMapperPlus\CustomMapper\CustomMapper;
use Doctrine\Common\Collections\Collection;

class CartMapper extends CustomMapper
{
    private $productManager;
    private $cartManager;

    /**
     * CartProductMapper constructor.
     * @param $productManager
     */
    public function __construct(ProductManagerInterface $productManager, CartManagerInterface $cartManager)
    {
        $this->productManager = $productManager;
        $this->cartManager = $cartManager;
    }

    /**
     * @inheritdoc
     */
    public function mapToObject($source, $destination)
    {
        if(!($source instanceof Cart) || !($destination instanceof CartDTO)) {
            //@todo: throw exception
        }

        $destination->id = $source->getId();
        $destination->currency = $source->getCurrency();

        $productIds = [];
        foreach ($source->getProducts() as $cartProduct) {
            $productIds[] = $cartProduct->getProductId();
        }

        $destination->products = [];

        $prefetchedProducts = $this->productManager->findMultiple($productIds, $source->getCurrency());

        foreach ($source->getProducts() as $cartProduct) {
            foreach ($prefetchedProducts as $productDTO) {
                if($productDTO->id == $cartProduct->getProductId()) {
                    $cartProductDTO = new CartProductDTO();
                    $cartProductDTO->product = $productDTO;
                    $cartProductDTO->quantity = $cartProduct->getQuantity();

                    $destination->products[] = $cartProductDTO;
                }
            }
        }

        $destination->total = $this->cartManager->getCartTotal($destination);

        return $destination;
    }
}