<?php

namespace Omega\Cyberkonsultant\Observer;

use Cyberkonsultant\DTO\Event;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Omega\Cyberkonsultant\Client\ApiClient;
use Omega\Cyberkonsultant\Cookie\UuidCookie;
use Psr\Log\LoggerInterface;

class PurchaseCompleteObserver implements ObserverInterface
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var UuidCookie
     */
    private $uuidCookie;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ApiClient                  $apiClient,
        UuidCookie                 $uuidCookie,
        ProductRepositoryInterface $productRepository,
        LoggerInterface            $logger
    ) {
        $this->apiClient = $apiClient;
        $this->uuidCookie = $uuidCookie;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        /** @var OrderInterface|null $order */
        $order = $observer->getData('order');

        /** @var Quote $quote */
        $quote = $observer->getData('quote');

        try {
            foreach ($quote->getAllVisibleItems() as $item) {
                if ($option = $item->getOptionByCode('simple_product')) {
                    $product = $option->getProduct();

                    $this->apiClient->trackEvent(
                        Event::PURCHASE,
                        $this->uuidCookie->get(),
                        $product->getId(),
                        $product->getCategoryIds()[0],
                        $item->getPriceInclTax(),
                        $order->getQuoteId()
                    );
                } else {
                    $product = $item->getProduct();

                    $this->apiClient->trackEvent(
                        Event::PURCHASE,
                        $this->uuidCookie->get(),
                        $product->getId(),
                        $product->getCategoryIds()[0],
                        $item->getPriceInclTax(),
                        $order->getQuoteId()
                    );
                }
            }

            $this->apiClient->updateUserFromOrder($this->uuidCookie->get(), $order);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
