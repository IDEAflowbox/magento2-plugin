<?php

namespace Omega\Cyberkonsultant\Cron;

use Omega\Cyberkonsultant\Client\ApiClient;
use Omega\Cyberkonsultant\Provider\AttributesProvider;
use Omega\Cyberkonsultant\Provider\CategoriesProvider;
use Omega\Cyberkonsultant\Provider\ProductsProvider;
use Psr\Log\LoggerInterface;

class Feed
{
    private $logger;
    private $attributesProvider;
    private $categoriesProvider;
    private $productsProvider;
    private $apiClient;

    public function __construct(
        AttributesProvider $attributesProvider,
        CategoriesProvider $categoriesProvider,
        ProductsProvider   $productsProvider,
        ApiClient          $apiClient,
        LoggerInterface    $logger
    ) {
        $this->attributesProvider = $attributesProvider;
        $this->categoriesProvider = $categoriesProvider;
        $this->productsProvider = $productsProvider;
        $this->apiClient = $apiClient;
        $this->logger = $logger;
    }

    public function execute()
    {
        try {
            $attributes = $this->attributesProvider->getAttributes();
            $this->apiClient->sendAttributes($attributes->getItems());

            $categories = $this->categoriesProvider->getCategories();
            $this->apiClient->sendCategories($categories->getItems());

            $transactionId = $this->apiClient->beginProductsTransaction();
            $page = 1;
            do {
                $products = $this->productsProvider->getProductData($page);
                $this->apiClient->appendProducts($transactionId, $products->getItems());
                $page = $products->getSearchCriteria()->getCurrentPage() + 1;
            } while ($products->getSearchCriteria()->getCurrentPage() < ($products->getTotalCount() / $products->getSearchCriteria()->getPageSize()));
            $this->apiClient->performProductsTransaction($transactionId);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
