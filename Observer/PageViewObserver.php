<?php

namespace Omega\Cyberkonsultant\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omega\Cyberkonsultant\Client\ApiClient;
use Omega\Cyberkonsultant\Cookie\UuidCookie;
use Omega\Cyberkonsultant\Publisher\EventPublisher;
use Omega\Cyberkonsultant\Publisher\PageEventPublisher;
use Omega\Cyberkonsultant\ValueObject\Event;
use Omega\Cyberkonsultant\ValueObject\PageEvent;
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
     * @var PageEventPublisher
     */
    private $pageEventPublisher;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        ApiClient                  $apiClient,
        UuidCookie                 $uuidCookie,
        LoggerInterface            $logger,
        EventPublisher             $publisher,
        PageEventPublisher         $pageEventPublisher,
        ProductRepositoryInterface $productRepository
    ) {
        $this->apiClient = $apiClient;
        $this->uuidCookie = $uuidCookie;
        $this->logger = $logger;
        $this->publisher = $publisher;
        $this->pageEventPublisher = $pageEventPublisher;
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
            $frameId = $request->getQuery()->get('frame_id');
            $id = (int)$request->getParam('id');

            if ($request->getQuery()->get('source') === 'recommendation_frame') {
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

            $pageEvent = new PageEvent(
                $this->uuidCookie->get(),
                new \DateTime(),
                PageEvent::VIEW,
                $id ?: null,
                $frameId,
                $request->getURI()->toString()
            );
            $this->pageEventPublisher->publish($pageEvent);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
