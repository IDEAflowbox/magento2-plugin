<?php
declare(strict_types=1);

namespace Omega\Cyberkonsultant\Consumer;

use Omega\Cyberkonsultant\Client\ApiClient;
use Omega\Cyberkonsultant\Provider\AttributesProvider;
use Omega\Cyberkonsultant\Provider\CategoriesProvider;
use Omega\Cyberkonsultant\Provider\ProductsProvider;
use Psr\Log\LoggerInterface;

class SendFeedConsumer
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

    public function process($message)
    {
        try {
            $attributes = $this->attributesProvider->getAttributes();
            $this->apiClient->sendAttributes($attributes->getItems());
            $categories = $this->categoriesProvider->getCategories();
            $this->apiClient->sendCategories($categories->getItems());
            $products = $this->productsProvider->getProductData();
            $this->apiClient->sendProducts($products->getItems());
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
