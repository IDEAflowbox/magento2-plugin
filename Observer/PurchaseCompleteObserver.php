<?php

namespace Omega\Cyberkonsultant\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Omega\Cyberkonsultant\Client\ApiClient;
use Omega\Cyberkonsultant\Cookie\UuidCookie;
use Omega\Cyberkonsultant\Publisher\EventPublisher;
use Omega\Cyberkonsultant\ValueObject\Event;
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventPublisher
     */
    private $publisher;

    public function __construct(
        ApiClient       $apiClient,
        UuidCookie      $uuidCookie,
        LoggerInterface $logger,
        EventPublisher $publisher
    ) {
        $this->apiClient = $apiClient;
        $this->uuidCookie = $uuidCookie;
        $this->logger = $logger;
        $this->publisher = $publisher;
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
                } else {
                    $product = $item->getProduct();
                }

                $event = new Event(
                    Event::PURCHASE,
                    $this->uuidCookie->get(),
                    $product->getId(),
                    isset($product->getCategoryIds()[0]) ? $product->getCategoryIds()[0] : null,
                    $item->getPriceInclTax(),
                    $order->getQuoteId(),
                    null,
                    (int) $item->getQty(),
                    $order->getIncrementId()
                );
                $this->publisher->publish($event);
            }

            $this->apiClient->updateUserFromOrder($this->uuidCookie->get(), $order);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
