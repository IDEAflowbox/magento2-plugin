<?php

namespace Omega\Cyberkonsultant\Client;

use Cyberkonsultant\Builder\EventBuilder;
use Cyberkonsultant\Builder\PageEventBuilder;
use Cyberkonsultant\Cyberkonsultant;
use Cyberkonsultant\DTO\SuccessResponse;
use Cyberkonsultant\DTO\User;
use Cyberkonsultant\Scope\Shop;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Omega\Cyberkonsultant\Helper\Configuration;
use Omega\Cyberkonsultant\Mapper\AttributeMapper;
use Omega\Cyberkonsultant\Mapper\CategoryMapper;
use Omega\Cyberkonsultant\Mapper\ProductMapper;

class ApiClient
{
    private $productMapper;
    private $categoryMapper;
    private $configuration;
    private $attributeMapper;
    private $countryInformationAcquirer;

    public function __construct(
        ProductMapper                       $productMapper,
        CategoryMapper                      $categoryMapper,
        AttributeMapper                     $attributeMapper,
        Configuration                       $configuration,
        CountryInformationAcquirerInterface $countryInformationAcquirer
    ) {
        $this->productMapper = $productMapper;
        $this->categoryMapper = $categoryMapper;
        $this->attributeMapper = $attributeMapper;
        $this->configuration = $configuration;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
    }

    /**
     * @param ProductInterface[] $mageProducts
     * @return SuccessResponse
     * @throws \Cyberkonsultant\Exception\ClientException
     * @throws \Cyberkonsultant\Exception\CyberkonsultantSDKException
     * @throws \Cyberkonsultant\Exception\ServerException
     * @throws \Unirest\Exception
     */
    public function sendProducts(array $mageProducts)
    {
        $products = $this->productMapper->mapToCKProducts($mageProducts);
        return $this->getShopScope()->product->createMany($products);
    }

    public function beginProductsTransaction(): string
    {
        return $this->getShopScope()->productsTransaction->beginTransaction();
    }

    public function appendProducts(string $transactionId, array $mageProducts): SuccessResponse
    {
        $products = $this->productMapper->mapToCKProducts($mageProducts);
        return $this->getShopScope()->productsTransaction->append($transactionId, $products);
    }

    public function performProductsTransaction(string $transactionId): SuccessResponse
    {
        return $this->getShopScope()->productsTransaction->perform($transactionId);
    }

    /**
     * @param CategoryInterface[] $mageCategories
     * @return SuccessResponse
     * @throws \Cyberkonsultant\Exception\ClientException
     * @throws \Cyberkonsultant\Exception\CyberkonsultantSDKException
     * @throws \Cyberkonsultant\Exception\ServerException
     * @throws \Unirest\Exception
     */
    public function sendCategories(array $mageCategories)
    {
        $categories = $this->categoryMapper->mapToCKCategories($mageCategories);
        return $this->getShopScope()->category->createMany($categories);
    }

    /**
     * @param ProductAttributeInterface[] $attributes
     * @return SuccessResponse
     * @throws \Cyberkonsultant\Exception\ClientException
     * @throws \Cyberkonsultant\Exception\CyberkonsultantSDKException
     * @throws \Cyberkonsultant\Exception\ServerException
     * @throws \Unirest\Exception
     */
    public function sendAttributes(array $attributes)
    {
        $features = $this->attributeMapper->mapToCKFeatures($attributes);
        return $this->getShopScope()->feature->createMany($features);
    }

    /**
     * TODO: Use Event ValueObject
     * @param $type
     * @param $userId
     * @param $productId
     * @param $categoryId
     * @param $price
     * @param null $cartId
     * @param \DateTime|null $cartId
     * @param string|null $frameId
     * @return void
     * @throws \Cyberkonsultant\Exception\ClientException
     * @throws \Cyberkonsultant\Exception\CyberkonsultantSDKException
     * @throws \Cyberkonsultant\Exception\ServerException
     * @throws \Unirest\Exception
     */
    public function trackEvent(
        $type,
        $userId,
        $productId,
        $categoryId,
        $price,
        $cartId = null,
        \DateTime $eventTime = null,
        $frameId = null
    ) {
        $eventBuilder = new EventBuilder();
        $event = $eventBuilder
            ->setUserId($userId)
            ->setProductId($productId)
            ->setCategoryId($categoryId)
            ->setPrice($price)
            ->setType($type);

        if ($cartId) {
            $event->setCartId($cartId);
        }

        if ($eventTime) {
            $event->setEventTime($eventTime);
        }

        if ($frameId) {
            $event->setFrameId($frameId);
        }

        $this->getShopScope()->event->create($event->getResult());
    }

    public function trackCrmEvent($userUuid, $type, $url, $productId = null, $frameId = null)
    {
        $pageEventBuilder = new PageEventBuilder();

        $pageEventBuilder
            ->setType($type)
            ->setUrl($url)
            ->setProductId($productId)
            ->setUserId($userUuid)
            ->setFrameId($frameId);

        $this->getCrmScope()->pageEvent->create($pageEventBuilder->getResult());
    }

    /**
     * @param string $uuid
     * @param CustomerInterface $customer
     * @return void
     * @throws \Cyberkonsultant\Exception\ClientException
     * @throws \Cyberkonsultant\Exception\CyberkonsultantSDKException
     * @throws \Cyberkonsultant\Exception\ServerException
     * @throws \Unirest\Exception
     */
    public function updateUser($uuid, $customer)
    {
        $user = $this->getShopScope()->user->find($uuid);
        $user->setFirstName($customer->getFirstname());
        $user->setLastName($customer->getLastname());
        $user->setEmail($customer->getEmail());
        $user->setUsername($customer->getEmail());

        if ($customer->getGender() === 1) {
            $user->setSex('male');
        }

        if ($customer->getGender() === 2) {
            $user->setSex('female');
        }

        foreach ($customer->getAddresses() as $address) {
            if ($address->getId() === $customer->getDefaultBilling()) {
                $user->setPhoneNumber($address->getTelephone());
                $user->setPostcode($address->getPostcode());
                $user->setCity($address->getCity());
                $countryInfo = $this->countryInformationAcquirer->getCountryInfo($address->getCountryId());
                $user->setCountry($countryInfo->getFullNameEnglish());
            }
        }

        $this->getShopScope()->user->update($user);
    }

    public function updateUserFromOrder(string $uuid, OrderInterface $order)
    {
        $user = $this->getShopScope()->user->find($uuid);
        $user->setFirstName($order->getCustomerFirstname());
        $user->setLastName($order->getCustomerLastname());
        $user->setEmail($order->getCustomerEmail());
        $user->setUsername($order->getCustomerEmail());

        if ($order->getCustomerGender() === 1) {
            $user->setSex('male');
        }

        if ($order->getCustomerGender() === 2) {
            $user->setSex('female');
        }

        foreach ($order->getAddresses() as $address) {
            if ($address->getId() === $order->getBillingAddressId()) {
                $user->setPhoneNumber($address->getTelephone());
                $user->setPostcode($address->getPostcode());
                $user->setCity($address->getCity());
                $countryInfo = $this->countryInformationAcquirer->getCountryInfo($address->getCountryId());
                $user->setCountry($countryInfo->getFullNameEnglish());
            }
        }

        $this->getShopScope()->user->update($user);
    }

    /**
     * @param string $email
     * @return User
     * @throws \Cyberkonsultant\Exception\ClientException
     * @throws \Cyberkonsultant\Exception\CyberkonsultantSDKException
     * @throws \Cyberkonsultant\Exception\ServerException
     * @throws \Unirest\Exception
     */
    public function findUserByEmail($email)
    {
        return $this->getShopScope()->user->findByEmail($email);
    }

    /**
     * @param string $userId
     * @return array
     */
    public function getRecommendationFrames($userId)
    {
        $response = $this->createClient()->get(sprintf('/shop/frames/ready/%s', $userId));
        return \json_decode($response->raw_body, true);
    }

    /**
     * @param string $email
     * @param string $finalUserId
     * @return void
     * @throws \Cyberkonsultant\Exception\ClientException
     * @throws \Cyberkonsultant\Exception\CyberkonsultantSDKException
     * @throws \Cyberkonsultant\Exception\ServerException
     * @throws \Unirest\Exception
     */
    public function mergeUsers($email, $finalUserId)
    {
        $user = $this->findUserByEmail($email);
        $this->getShopScope()->user->merge([$user->getId(), $finalUserId], $finalUserId);
    }

    /**
     * @return Shop
     */
    private function getShopScope()
    {
        return $this->createClient()->getShopScope();
    }

    /**
     * @return \Cyberkonsultant\Scope\Crm
     * @throws \Cyberkonsultant\Exception\CyberkonsultantSDKException
     */
    private function getCrmScope()
    {
        return $this->createClient()->getCrmScope();
    }

    /**
     * @return Cyberkonsultant
     * @throws \Cyberkonsultant\Exception\CyberkonsultantSDKException
     */
    private function createClient()
    {
        return new Cyberkonsultant([
            'api_url' => $this->configuration->getGeneralConfig('api_host'),
            'access_token' => $this->configuration->getGeneralConfig('api_key'),
        ]);
    }
}
