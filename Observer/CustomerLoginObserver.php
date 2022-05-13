<?php

namespace Omega\Cyberkonsultant\Observer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omega\Cyberkonsultant\Client\ApiClient;
use Omega\Cyberkonsultant\Cookie\UuidCookie;
use Psr\Log\LoggerInterface;

class CustomerLoginObserver implements ObserverInterface
{
    private $apiClient;
    private $uuidCookie;
    private $logger;

    public function __construct(
        ApiClient       $apiClient,
        UuidCookie      $uuidCookie,
        LoggerInterface $logger
    ) {
        $this->apiClient = $apiClient;
        $this->uuidCookie = $uuidCookie;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        /** @var CustomerInterface $customer */
        $customer = $observer->getData('customer');

        try {
            $email = $customer->getEmail();
            $this->apiClient->mergeUsers($email, $this->uuidCookie->get());
        } catch (\Exception $e) {
            $this->logger->error($e);
        }

        try {
            $this->apiClient->updateUser($this->uuidCookie->get(), $customer);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
