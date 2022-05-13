<?php

namespace Omega\Cyberkonsultant\Mapper\Product;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;

class ConfigurableProductMapper implements ProductMapperInterface
{
    private $simpleProductMapper;

    public function __construct(
        SimpleProductMapper $simpleProductMapper
    ) {
        $this->simpleProductMapper = $simpleProductMapper;
    }

    /**
     * @inheritdoc
     */
    public function map(ProductInterface $mageProduct): array
    {
        $simpleProducts = $mageProduct->getTypeInstance()->getUsedProducts($mageProduct);

        $result = [];
        foreach ($simpleProducts as $simpleProduct) {
            $product = $this->simpleProductMapper->map($simpleProduct)[0];
            $product->setUrl($mageProduct->getProductUrl());
            $product->setDescription($mageProduct->getDescription());
            $result[] = $product;
        }
        return $result;
    }
}
