<?php

namespace Omega\Cyberkonsultant\Mapper\Product;

use Cyberkonsultant\DTO\Product;
use Magento\Catalog\Api\Data\ProductInterface;

interface ProductMapperInterface
{
    /**
     * @param ProductInterface $mageProduct
     * @return Product[]
     */
    public function map(ProductInterface $mageProduct): array;
}
