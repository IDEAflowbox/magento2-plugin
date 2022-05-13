<?php

namespace Omega\Cyberkonsultant\Observer;

use Cyberkonsultant\DTO\Event;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omega\Cyberkonsultant\Client\ApiClient;
use Omega\Cyberkonsultant\Cookie\UuidCookie;
use Psr\Log\LoggerInterface;

class ProductViewObserver implements ObserverInterface
{
    private $apiClient;
    private $uuidCookie;
    private $logger;

    public function __construct(ApiClient $apiClient, UuidCookie $uuidCookie, LoggerInterface $logger)
    {
        $this->apiClient = $apiClient;
        $this->uuidCookie = $uuidCookie;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        if (!$this->uuidCookie->get()) {
            return;
        }

        /** @var Product $product */
        $product = $observer->getData('product');

        try {
            $this->apiClient->trackEvent(
                Event::VIEW,
                $this->uuidCookie->get(),
                $product->getId(),
                $product->getCategoryId(),
                $product->getPrice()
            );
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
