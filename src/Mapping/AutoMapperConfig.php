<?php

namespace App\Mapping;

use App\DTO\CartDTO;
use App\DTO\PriceDTO;
use App\DTO\ProductDTO;
use App\Entity\Cart;
use App\Entity\Price;
use App\Entity\Product;
use AutoMapperPlus\AutoMapperInterface;
use AutoMapperPlus\AutoMapperPlusBundle\AutoMapperConfiguratorInterface;
use AutoMapperPlus\Configuration\AutoMapperConfigInterface;
use AutoMapperPlus\MappingOperation\Operation;

class AutoMapperConfig implements AutoMapperConfiguratorInterface
{
    public function configure(AutoMapperConfigInterface $config): void
    {
        $addAccessor = new MutatorPropertyAccessor();
        $addAccessor->setSetterPrefix("add");
        $addAccessor->setIgnoreNulls(true);

        $config->registerMapping(PriceDTO::class, Price::class)
            ->reverseMap();

        $config->registerMapping(ProductDTO::class, Product::class)
            ->dontSkipConstructor()
            ->forMember("prices", Operation::mapTo(Price::class))
            ->getOptions()->setPropertyAccessor($addAccessor);

        $config->registerMapping(Product::class, ProductDTO::class)
            ->forMember("prices", Operation::mapTo(PriceDTO::class));

        $config->registerMapping(Cart::class, CartDTO::class);
    }
}