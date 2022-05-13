<?php

namespace Omega\Cyberkonsultant\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Omega\Cyberkonsultant\Cookie\UuidCookie;
use Omega\Cyberkonsultant\Generator\UuidGenerator;

class UuidCookieObserver implements ObserverInterface
{
    /**
     * @var UuidCookie
     */
    private $uuidCookie;

    public function __construct(UuidCookie $uuidCookie)
    {
        $this->uuidCookie = $uuidCookie;
    }

    public function execute(Observer $observer)
    {
        /** @var RequestInterface $request */
        $request = $observer->getData('request');

        if (!$request->getCookie(UuidCookie::COOKIE_NAME)) {
            $this->uuidCookie->set(UuidGenerator::generate());
        }
    }
}
