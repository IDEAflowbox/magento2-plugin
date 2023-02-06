<?php

namespace Omega\Cyberkonsultant\ValueObject;

class PageEvent
{
    public const VIEW = 'view';

    /**
     * @var string
     */
    private $userId;

    /**
     * @var \DateTime
     */
    private $eventTime;

    /**
     * @var string
     */
    private $eventType;

    /**
     * @var string|null
     */
    private $productId;

    /**
     * @var string|null
     */
    private $frameId;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @param string $userId
     * @param \DateTime $eventTime
     * @param string $eventType
     * @param string|null $productId
     * @param string|null $frameId
     * @param string|null $url
     */
    public function __construct(string $userId, \DateTime $eventTime, string $eventType, ?string $productId, ?string $frameId, ?string $url)
    {
        $this->userId = $userId;
        $this->eventTime = $eventTime;
        $this->eventType = $eventType;
        $this->productId = $productId;
        $this->frameId = $frameId;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @param string $userId
     */
    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return \DateTime
     */
    public function getEventTime(): \DateTime
    {
        return $this->eventTime;
    }

    /**
     * @param \DateTime $eventTime
     */
    public function setEventTime(\DateTime $eventTime): void
    {
        $this->eventTime = $eventTime;
    }

    /**
     * @return string
     */
    public function getEventType(): string
    {
        return $this->eventType;
    }

    /**
     * @param string $eventType
     */
    public function setEventType(string $eventType): void
    {
        $this->eventType = $eventType;
    }

    /**
     * @return string|null
     */
    public function getProductId(): ?string
    {
        return $this->productId;
    }

    /**
     * @param string|null $productId
     */
    public function setProductId(?string $productId): void
    {
        $this->productId = $productId;
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
     */
    public function setFrameId(?string $frameId): void
    {
        $this->frameId = $frameId;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     */
    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function __toArray(): array
    {
        return [
            'user_id' => $this->getUserId(),
            'event_time' => (new \DateTime())->format(\DateTime::ATOM),
            'event_type' => $this->getEventType(),
            'product_id' => $this->getProductId(),
            'frame_id' => $this->getFrameId(),
            'url' => $this->getUrl(),
        ];
    }
}
