<?php

namespace Omega\Cyberkonsultant\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omega\Cyberkonsultant\Client\ApiClient;
use Omega\Cyberkonsultant\Cookie\UuidCookie;
use Omega\Cyberkonsultant\Publisher\EventPublisher;
use Omega\Cyberkonsultant\ValueObject\Event;
use Psr\Log\LoggerInterface;

class PageViewObserver implements ObserverInterface
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

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        ApiClient                  $apiClient,
        UuidCookie                 $uuidCookie,
        LoggerInterface            $logger,
        EventPublisher             $publisher,
        ProductRepositoryInterface $productRepository
    ) {
        $this->apiClient = $apiClient;
        $this->uuidCookie = $uuidCookie;
        $this->logger = $logger;
        $this->publisher = $publisher;
        $this->productRepository = $productRepository;
    }

    public function execute(Observer $observer)
    {
        if (!$this->uuidCookie->get()) {
            return;
        }

        /** @var Http $request */
        $request = $observer->getData('request');

        if (!in_array($request->getModuleName(), ['cms', 'catalog', 'customer', 'checkout'])
            || $request->getControllerName() === 'noroute') {
            return;
        }

        if ($request->isAjax()) {
            return;
        }

        try {
            if ($request->getQuery()->get('source') === 'recommendation_frame') {
                $id = (int)$request->getParam('id');
                if ($id) {
                    $product = $this->productRepository->getById($id);
                    $event = new Event(
                        Event::RECOMMENDATION_FRAME,
                        $this->uuidCookie->get(),
                        $id,
                        isset($product->getCategoryIds()[0]) ? $product->getCategoryIds()[0] : null,
                        $product->getFinalPrice(),
                        null,
                        $request->getParam('frame_id') ?: null
                    );
                    $this->publisher->publish($event);
                }
            }

            $this->apiClient->trackCrmEvent($this->uuidCookie->get(), 'view', $request->getURI()->toString());
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
