<?php

namespace Omega\Cyberkonsultant\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omega\Cyberkonsultant\Cookie\UuidCookie;
use Omega\Cyberkonsultant\Publisher\EventPublisher;
use Omega\Cyberkonsultant\ValueObject\Event;
use Psr\Log\LoggerInterface;

class CheckoutCartProductAddAfter implements ObserverInterface
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

    public function __construct(UuidCookie $uuidCookie, LoggerInterface $logger, EventPublisher $publisher)
    {
        $this->uuidCookie = $uuidCookie;
        $this->logger = $logger;
        $this->publisher = $publisher;
    }

    public function execute(Observer $observer)
    {
        try {
            $item = $observer->getQuoteItem();
            if ($option = $item->getOptionByCode('simple_product')) {
                /** @var Product $product */
                $product = $option->getProduct();
            } else {
                /** @var Product $product */
                $product = $item->getProduct();
            }

            $event = new Event(
                Event::CART,
                $this->uuidCookie->get(),
                $product->getId(),
                isset($product->getCategoryIds()[0]) ? $product->getCategoryIds()[0] : null,
                $product->getPrice(),
                null,
                null,
                (int) $item->getQty()
            );
            $this->publisher->publish($event);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
