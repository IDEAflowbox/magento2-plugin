<?php

namespace Omega\Cyberkonsultant\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omega\Cyberkonsultant\Cookie\UuidCookie;
use Omega\Cyberkonsultant\Publisher\EventPublisher;
use Omega\Cyberkonsultant\ValueObject\Event;
use Psr\Log\LoggerInterface;

class WishlistAddProductObserver implements ObserverInterface
{
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
        UuidCookie      $uuidCookie,
        LoggerInterface $logger,
        EventPublisher  $publisher
    ) {
        $this->uuidCookie = $uuidCookie;
        $this->logger = $logger;
        $this->publisher = $publisher;
    }

    public function execute(Observer $observer)
    {
        /** @var Product $product */
        $product = $observer->getData('product');

        try {
            $event = new Event(
                Event::WISHLIST,
                $this->uuidCookie->get(),
                $product->getId(),
                $product->getCategoryIds()[0],
                $product->getFinalPrice()
            );
            $this->publisher->publish($event);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
