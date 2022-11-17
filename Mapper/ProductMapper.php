<?php

namespace Omega\Cyberkonsultant\Mapper;

use Cyberkonsultant\DTO\Product;
use Magento\Catalog\Api\Data\ProductInterface;
use Omega\Cyberkonsultant\Mapper\Product\ConfigurableProductMapper;
use Omega\Cyberkonsultant\Mapper\Product\SimpleProductMapper;
use Psr\Log\LoggerInterface;

class ProductMapper
{
    private $configurableProductMapper;
    private $simpleProductMapper;
    private $logger;

    public function __construct(
        ConfigurableProductMapper $configurableProductMapper,
        SimpleProductMapper $simpleProductMapper,
        LoggerInterface $logger
    ) {
        $this->configurableProductMapper = $configurableProductMapper;
        $this->logger = $logger;
        $this->simpleProductMapper = $simpleProductMapper;
    }

    /**
     * @param ProductInterface[] $mageProducts
     * @return Product[]
     */
    public function mapToCKProducts($mageProducts): array
    {
        $products = [];

        foreach ($mageProducts as $mageProduct) {
            if (!$mageProduct->isSaleable()) {
                continue;
            }

            try {
                // TODO: IF YOU ARE READING THIS -> YOU CAN CONSIDER FACTORY / FACTORY METHOD OR OTHER FANCY STUFF :)
                // TODO: BUT DOES IT MAKE SENSE TO PLAY WITH THIS IN MAGENTO ? FOR ME NOT :)
                switch ($mageProduct->getTypeId()) {
                    // TODO: FIND CONSTANTS FOR PRODUCT TYPES IN MAGENTO... IF YOU CAN
                    case 'configurable':
                        $products[] = $this->configurableProductMapper->map($mageProduct);
                        break;
                    case 'simple':
                        $products[] = $this->simpleProductMapper->map($mageProduct);
                        break;
                    default:
                        $this->logger->debug(
                            sprintf('Product mapper for type: "%s" was not found.', $mageProduct->getTypeId())
                        );
                }
            } catch (\Throwable $e) {
                $this->logger->error(sprintf('Product with id: "%s" is invalid', $mageProduct->getId()));
            }
        }

        return $products;
    }
}
