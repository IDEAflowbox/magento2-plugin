<?php

namespace Omega\Cyberkonsultant\Mapper\Product;

use Cyberkonsultant\Builder\ProductBuilder;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Data;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Store\Model\StoreManagerInterface;

class SimpleProductMapper implements ProductMapperInterface
{
    private $storeManager;
    private $taxHelper;
    private $stockState;
    private $productRepository;

    public function __construct(
        StoreManagerInterface $storeManager,
        Data $taxHelper,
        StockStateInterface $stockState,
        ProductRepositoryInterface $productRepository
    ) {
        $this->storeManager = $storeManager;
        $this->taxHelper = $taxHelper;
        $this->stockState = $stockState;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function map(ProductInterface $mageProduct): array
    {
        $mageProduct = $this->productRepository->getById($mageProduct->getId());
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
                $this->getRegularPrice($mageProduct) !== $mageProduct->getFinalPrice(
                ) ? $mageProduct->getFinalPrice() : null
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
            if (!$attribute->getIsUserDefined()) {
                continue;
            }

            $attributeValue = $mageProduct->getData($attribute->getName());
            if (empty($attribute->getOptions()) || !$attributeValue) {
                continue;
            }

            foreach (explode(',', $attributeValue) as $choiceId) {
                $productBuilder->addFeature($attribute->getAttributeId(), $choiceId);
            }

        }

        return [$productBuilder->getResult()];
    }

    private function getRegularPrice(ProductInterface $product): float
    {
        $regularPrice = $product->getPriceInfo()->getPrice('regular_price');
        return $regularPrice->getValue();
    }

    private function getMediaBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }

    private function getStockQty(ProductInterface $product): int
    {
        return (int)$this->stockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
    }
}
