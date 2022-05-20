<?php

namespace Omega\Cyberkonsultant\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omega\Cyberkonsultant\Cookie\UuidCookie;
use Omega\Cyberkonsultant\Publisher\EventPublisher;
use Omega\Cyberkonsultant\ValueObject\Event;
use Psr\Log\LoggerInterface;

class ProductViewObserver implements ObserverInterface
{
    private $uuidCookie;
    private $logger;
    private $publisher;

    public function __construct(UuidCookie $uuidCookie, LoggerInterface $logger, EventPublisher $publisher)
    {
        $this->uuidCookie = $uuidCookie;
        $this->logger = $logger;
        $this->publisher = $publisher;
    }

    public function execute(Observer $observer)
    {
        if (!$this->uuidCookie->get()) {
            return;
        }

        /** @var Product $product */
        $product = $observer->getData('product');

        try {
            $event = new Event(
                Event::VIEW,
                $this->uuidCookie->get(),
                $product->getId(),
                $product->getCategoryId(),
                $product->getPrice()
            );
            $this->publisher->publish($event);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
