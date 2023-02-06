<?php

namespace Omega\Cyberkonsultant\ValueObject;

class Event
{
    public const VIEW = 'view';
    public const CART = 'cart';
    public const WISHLIST = 'wishlist';
    public const PURCHASE = 'purchase';
    public const RECOMMENDATION_FRAME = 'recommendation_frame';

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $uuid;

    /**
     * @var int
     */
    private $productId;

    /**
     * @var int|null
     */
    private $categoryId;

    /**
     * @var float|null
     */
    private $price;

    /**
     * @var int|null
     */
    private $cartId;

    /**
     * @var string|null
     */
    private $frameId;

    /**
     * @param string $type
     * @param string $uuid
     * @param int|null $productId
     * @param int|null $categoryId
     * @param float|null $price
     * @param int|null $cartId
     */
    public function __construct(
        string  $type,
        string  $uuid,
        ?int    $productId,
        ?int    $categoryId = null,
        ?float  $price = null,
        ?int    $cartId = null,
        ?string $frameId = null
    ) {
        $this->type = $type;
        $this->uuid = $uuid;
        $this->productId = $productId;
        $this->categoryId = $categoryId;
        $this->price = $price;
        $this->cartId = $cartId;
        $this->frameId = $frameId;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Event
     */
    public function setType(string $type): Event
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     * @return Event
     */
    public function setUuid(string $uuid): Event
    {
        $this->uuid = $uuid;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getProductId(): ?int
    {
        return $this->productId;
    }

    /**
     * @param int|null $productId
     * @return Event
     */
    public function setProductId(?int $productId): Event
    {
        $this->productId = $productId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    /**
     * @param int|null $categoryId
     * @return Event
     */
    public function setCategoryId(?int $categoryId): Event
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPrice(): ?float
    {
        return $this->price;
    }

    /**
     * @param float|null $price
     * @return Event
     */
    public function setPrice(?float $price): Event
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCartId(): ?int
    {
        return $this->cartId;
    }

    /**
     * @param int|null $cartId
     * @return Event
     */
    public function setCartId(?int $cartId): Event
    {
        $this->cartId = $cartId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFrameId(): ?string
    {
        return $this->frameId;
    }

    /**
     * @param string|null $frameId
     * @return Event
     */
    public function setFrameId(?string $frameId): Event
    {
        $this->frameId = $frameId;
        return $this;
    }

    public function __toArray(): array
    {
        return [
            'type' => $this->getType(),
            'uuid' => $this->getUuid(),
            'productId' => $this->getProductId(),
            'categoryId' => $this->getCategoryId(),
            'price' => $this->getPrice(),
            'frameId' => $this->getFrameId(),
            'eventTime' => (new \DateTime())->format(\DateTime::ATOM),
        ];
    }
}
