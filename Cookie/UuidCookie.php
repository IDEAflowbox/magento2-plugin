<?php

namespace Omega\Cyberkonsultant\Cookie;

use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Session\SessionManagerInterface;

class UuidCookie
{
    const COOKIE_NAME = 'ckuuid';
    const COOKIE_TTL = 86400 * 365;

    /**
     * CookieManager
     *
     * @var CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SessionManagerInterface $sessionManager
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
    }

    /**
     * @return string
     */
    public function get()
    {
        return $this->cookieManager->getCookie(self::COOKIE_NAME);
    }

    /**
     * @param string $value
     * @param int $duration
     * @return void
     */
    public function set($value, $duration = self::COOKIE_TTL)
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration($duration)
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain());

        $this->cookieManager->setPublicCookie(
            self::COOKIE_NAME,
            $value,
            $metadata
        );
    }

    /**
     * @return void
     */
    public function delete()
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(0)
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain());

        $this->cookieManager->deleteCookie(
            self::COOKIE_NAME,
            $metadata
        );
    }
}
