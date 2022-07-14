<?php

namespace Omega\Cyberkonsultant\Mapper\Product;

use Cyberkonsultant\Builder\ProductBuilder;
use Cyberkonsultant\DTO\Product;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Data;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Store\Model\StoreManagerInterface;

class ConfigurableProductMapper implements ProductMapperInterface
{
    private $storeManager;
    private $taxHelper;
    private $stockState;
    private $simpleProductMapper;

    public function __construct(
        StoreManagerInterface $storeManager,
        Data                  $taxHelper,
        StockStateInterface   $stockState,
        SimpleProductMapper   $simpleProductMapper
    ) {
        $this->storeManager = $storeManager;
        $this->taxHelper = $taxHelper;
        $this->stockState = $stockState;
        $this->simpleProductMapper = $simpleProductMapper;
    }

    /**
     * @inheritdoc
     */
    public function map(ProductInterface $mageProduct): array
    {
        $products = $this->mapChildrenProducts($mageProduct);
        $products[] = $this->mapParentProduct($mageProduct);
        return $products;
    }

    /**
     * @param ProductInterface $mageProduct
     * @return Product[]
     */
    private function mapChildrenProducts(ProductInterface $mageProduct): array
    {
        $simpleProducts = $mageProduct->getTypeInstance()->getUsedProducts($mageProduct);

        $result = [];
        foreach ($simpleProducts as $simpleProduct) {
            try {
                $product = $this->simpleProductMapper->map($simpleProduct)[0];
                $product->setUrl($mageProduct->getProductUrl());
                $product->setDescription($mageProduct->getDescription());
                $product->setName($mageProduct->getName());
                $product->setParentId($mageProduct->getId());
                $result[] = $product;
            } catch (\Exception $e) {
                continue;
            }
        }
        return $result;
    }

    private function mapParentProduct(ProductInterface $mageProduct): Product
    {
        $productBuilder = new ProductBuilder();

        $description = $mageProduct->getCustomAttribute(ProductAttributeInterface::CODE_DESCRIPTION);

        $productBuilder
            ->setName($mageProduct->getName())
            ->setId($mageProduct->getId())
            ->setUrl($mageProduct->getProductUrl())
            ->setNetPrice($this->getRegularPrice($mageProduct))
            ->setImage($this->getMediaBaseUrl() . 'pub/media/catalog/product' . $mageProduct->getImage())
            ->setGrossPrice($this->taxHelper->getTaxPrice($mageProduct, $this->getRegularPrice($mageProduct), true))
            ->setGrossSalePrice(
                $this->getRegularPrice($mageProduct) !== $mageProduct->getFinalPrice() ? $mageProduct->getFinalPrice(
                ) : null
            )
            ->setSku($mageProduct->getSku())
            ->setCurrency($this->storeManager->getStore()->getCurrentCurrency()->getCode())
            ->setStock($this->getStockQty($mageProduct));

        if ($description) {
            $productBuilder->setDescription($description->getValue());
        }

        foreach ($mageProduct->getCategoryIds() ?: [] as $categoryId) {
            $productBuilder->addCategory($categoryId);
        }

        foreach ($mageProduct->getAttributes() as $attribute) {
            if (!$attribute->getAttributeId() || !$attribute->getIsVisibleOnFront()) {
                continue;
            }

            if (!is_numeric($mageProduct->getData($attribute->getName()))) {
                continue;
            }

            $productBuilder->addFeature($attribute->getAttributeId(), $mageProduct->getData($attribute->getName()));
        }

        return $productBuilder->getResult();
    }

    private function getRegularPrice(ProductInterface $mageProduct): float
    {
        $basePrice = $mageProduct->getPriceInfo()->getPrice('regular_price');
        return $basePrice->getMinRegularAmount()->getValue();
    }

    private function getMediaBaseUrl(): string
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    private function getStockQty(ProductInterface $mageProduct): int
    {
        $usedProducts = $mageProduct->getTypeInstance()->getUsedProducts($mageProduct);
        $totalStock = 0;
        foreach ($usedProducts as $simple) {
            $totalStock += (int)$this->stockState->getStockQty(
                $simple->getId(),
                $simple->getStore()->getWebsiteId()
            );
        }
        return $totalStock;
    }
}

