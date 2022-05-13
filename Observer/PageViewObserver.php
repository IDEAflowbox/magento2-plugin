<?php

namespace Omega\Cyberkonsultant\Observer;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omega\Cyberkonsultant\Client\ApiClient;
use Omega\Cyberkonsultant\Cookie\UuidCookie;
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

        /** @var Http $request */
        $request = $observer->getData('request');

        if (!in_array($request->getModuleName(), ['cms', 'catalog', 'customer', 'checkout']) || $request->getControllerName() === 'noroute') {
            return;
        }

        try {
            $this->apiClient->trackCrmEvent($this->uuidCookie->get(), 'view', $request->getURI()->toString());
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
