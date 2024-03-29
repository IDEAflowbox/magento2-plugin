<?php

namespace Omega\Cyberkonsultant\Mapper\Product;

use Cyberkonsultant\Builder\ProductBuilder;
use Cyberkonsultant\DTO\Product;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Data;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class SimpleProductMapper implements ProductMapperInterface
{
    private $storeManager;
    private $taxHelper;
    private $stockState;
    private $configurableType;
    private $moduleManager;
    private $productRepository;

    public function __construct(
        StoreManagerInterface $storeManager,
        Data $taxHelper,
        StockStateInterface $stockState,
        Configurable $configurableType,
        Manager $moduleManager,
        ProductRepositoryInterface $productRepository
    ) {
        $this->storeManager = $storeManager;
        $this->taxHelper = $taxHelper;
        $this->stockState = $stockState;
        $this->configurableType = $configurableType;
        $this->moduleManager = $moduleManager;
        $this->productRepository = $productRepository;
    }

    /**
     * @inheritdoc
     */
    public function map(ProductInterface $mageProduct): Product
    {
        $productBuilder = new ProductBuilder();
        $parentIds = $this->configurableType->getParentIdsByChild($mageProduct->getId());
        $parentId = array_shift($parentIds);

        $description = $mageProduct->getCustomAttribute(ProductAttributeInterface::CODE_DESCRIPTION);
        $image = $this->getMediaBaseUrl() . 'media/catalog/product' . $mageProduct->getImage();
        if (!$mageProduct->getImage()) {
            $parent = $this->productRepository->getById($parentId);
            $image = $this->getMediaBaseUrl() . 'media/catalog/product' . $parent->getImage();
        }

        $productBuilder
            ->setName($mageProduct->getName())
            ->setId($mageProduct->getId())
            ->setUrl($mageProduct->getProductUrl())
            ->setNetPrice($this->getRegularPrice($mageProduct))
            ->setImage((string)$image)
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

        if ($parentId) {
            $productBuilder->setParentId($parentId);
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

        return $productBuilder->getResult();
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
        if ($this->moduleManager->isEnabled('Magento_Inventory')) {
            $objectManager = ObjectManager::getInstance();
            $stockState = $objectManager->get('\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku');
            $qty = $stockState->execute($product->getSku());

            $ret = 0;
            foreach ($qty as $q) {
                $ret += $q['qty'];
            }

            ObjectManager::getInstance()->get(LoggerInterface::class)->info((string) $ret);
            return $ret;
        }

        return (int)$this->stockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
    }
}
