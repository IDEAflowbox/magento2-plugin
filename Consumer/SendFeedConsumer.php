<?php
declare(strict_types=1);

namespace Omega\Cyberkonsultant\Consumer;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Omega\Cyberkonsultant\Client\ApiClient;
use Omega\Cyberkonsultant\Provider\AttributesProvider;
use Omega\Cyberkonsultant\Provider\CategoriesProvider;
use Psr\Log\LoggerInterface;

class SendFeedConsumer
{
    private $logger;
    private $attributesProvider;
    private $categoriesProvider;
    private $apiClient;
    private $collectionFactory;

    public function __construct(
        AttributesProvider $attributesProvider,
        CategoriesProvider $categoriesProvider,
        ApiClient          $apiClient,
        LoggerInterface    $logger,
        CollectionFactory  $collectionFactory
    ) {
        $this->attributesProvider = $attributesProvider;
        $this->categoriesProvider = $categoriesProvider;
        $this->apiClient = $apiClient;
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
    }

    public function process($message)
    {
        try {
            $attributes = $this->attributesProvider->getAttributes();
            $this->apiClient->sendAttributes($attributes->getItems());

            $categories = $this->categoriesProvider->getCategories();
            $this->apiClient->sendCategories($categories->getItems());

            $transactionId = $this->apiClient->beginProductsTransaction();

            $collection = $this->collectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addAttributeToFilter(ProductInterface::VISIBILITY, Visibility::VISIBILITY_BOTH);
            $collection->addAttributeToFilter(ProductInterface::STATUS, 1);
            $collection->setPageSize(50);

            for ($page = 1; $page <= $collection->getLastPageNumber(); $page++) {
                $collection->setCurPage($page);
                $this->apiClient->appendProducts($transactionId, $collection);
                $collection->clear();
            }

            $this->apiClient->performProductsTransaction($transactionId);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
