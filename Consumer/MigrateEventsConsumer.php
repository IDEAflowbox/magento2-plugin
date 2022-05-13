<?php
declare(strict_types=1);

namespace Omega\Cyberkonsultant\Consumer;

use Cyberkonsultant\DTO\Event;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Omega\Cyberkonsultant\Client\ApiClient;
use Omega\Cyberkonsultant\Generator\UuidGenerator;
use Omega\Cyberkonsultant\Provider\OrderProvider;
use Psr\Log\LoggerInterface;

class MigrateEventsConsumer
{
    private const BATCH_SIZE = 50;
    private const TOPIC_NAME = 'cyberkonsultant.migrate.events';

    /**
     * @var OrderProvider
     */
    private $orderProvider;

    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    public function __construct(
        OrderProvider              $orderProvider,
        ApiClient                  $apiClient,
        LoggerInterface            $logger,
        ProductRepositoryInterface $productRepository,
        PublisherInterface         $publisher
    ) {
        $this->orderProvider = $orderProvider;
        $this->apiClient = $apiClient;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->publisher = $publisher;
    }

    public function process($message)
    {
        $orders = $this->orderProvider->getOrders(1, self::BATCH_SIZE);
        $this->processOrders($orders);

        if ($orders->getLastPageNumber() > 1) {
            $this->publisher->publish(self::TOPIC_NAME, '');
        }
    }

    private function processOrders(OrderSearchResultInterface $orders)
    {
        foreach ($orders->getItems() as $order) {
            $this->processOrder($order);
        }
    }

    private function processOrder(OrderInterface $order): void
    {
        try {
            $user = $this->apiClient->findUserByEmail($order->getCustomerEmail());
            $uuid = $user->getId();
        } catch (\Exception $e) {
            $uuid = UuidGenerator::generate();
        }

        foreach ($order->getAllItems() as $orderItem) {
            try {
                $product = $this->productRepository->get($orderItem->getSku());
            } catch (NoSuchEntityException $e) {
                $order->setData('migrated_to_ck_at', new \DateTime());
                $order->save();
                continue;
            }

            try {
                $this->sendEvents($uuid, $product, $orderItem, $order);

                $order->setData('migrated_to_ck_at', new \DateTime());
                $order->save();
            } catch (\Exception $e) {
                $this->logger->error($e, ['product' => $product->getData(), 'orderItem' => $orderItem->getData()]);
            }
        }

        try {
            $this->apiClient->updateUserFromOrder($uuid, $order);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }

    private function sendEvents(string $uuid, ProductInterface $product, $orderItem, OrderInterface $order): void
    {
        if (empty($product->getCategoryId()) && empty($product->getCategoryIds())) {
            return;
        }

        $this->apiClient->trackEvent(
            Event::CART,
            $uuid,
            $orderItem->getProductId(),
            $product->getCategoryId() ?: $product->getCategoryIds()[0],
            $orderItem->getPriceInclTax() ?: $orderItem->getParentItem()->getPriceInclTax(),
            null,
            $order->getCreatedAt() ? new \DateTime($order->getCreatedAt()) : null
        );

        $this->apiClient->trackEvent(
            Event::PURCHASE,
            $uuid,
            $orderItem->getProductId(),
            $product->getCategoryId() ?: $product->getCategoryIds()[0],
            $orderItem->getPriceInclTax() ?: $orderItem->getParentItem()->getPriceInclTax(),
            $order->getQuoteId(),
            $order->getCreatedAt() ? new \DateTime($order->getCreatedAt()) : null
        );
    }
}
