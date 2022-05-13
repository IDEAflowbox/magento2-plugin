<?php

namespace Omega\Cyberkonsultant\Observer;

use Cyberkonsultant\DTO\Event;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omega\Cyberkonsultant\Client\ApiClient;
use Omega\Cyberkonsultant\Cookie\UuidCookie;
use Psr\Log\LoggerInterface;

class CheckoutCartProductAddAfter implements ObserverInterface
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
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ApiClient $apiClient, UuidCookie $uuidCookie, LoggerInterface $logger)
    {
        $this->apiClient = $apiClient;
        $this->uuidCookie = $uuidCookie;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $item = $observer->getQuoteItem();
            if ($option = $item->getOptionByCode('simple_product')) {
                /** @var Product $product */
                $product = $option->getProduct();

                $this->apiClient->trackEvent(
                    Event::CART,
                    $this->uuidCookie->get(),
                    $product->getId(),
                    $product->getCategoryIds()[0],
                    $product->getPrice()
                );
            } else {
                /** @var Product $product */
                $product = $item->getProduct();

                $this->apiClient->trackEvent(
                    Event::CART,
                    $this->uuidCookie->get(),
                    $product->getId(),
                    $product->getCategoryIds()[0],
                    $product->getPrice()
                );
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
