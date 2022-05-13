<?php

namespace Omega\Cyberkonsultant\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omega\Cyberkonsultant\Client\ApiClient;
use Omega\Cyberkonsultant\Cookie\UuidCookie;
use Psr\Log\LoggerInterface;

class CustomerAccountEditedObserver implements ObserverInterface
{
    private $customerRepository;
    private $apiClient;
    private $uuidCookie;
    private $logger;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        ApiClient                   $apiClient,
        UuidCookie                  $uuidCookie,
        LoggerInterface             $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->apiClient = $apiClient;
        $this->uuidCookie = $uuidCookie;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        /** @var string $email */
        $email = $observer->getData('email');

        $customer = $this->customerRepository->get($email);

        try {
            $this->apiClient->updateUser($this->uuidCookie->get(), $customer);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
