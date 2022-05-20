<?php
declare(strict_types=1);

namespace Omega\Cyberkonsultant\Consumer;

use Omega\Cyberkonsultant\Client\ApiClient;
use Omega\Cyberkonsultant\ValueObject\Event;
use Psr\Log\LoggerInterface;

class TrackEventsConsumer
{
    private $logger;
    private $apiClient;

    public function __construct(LoggerInterface $logger, ApiClient $apiClient)
    {
        $this->logger = $logger;
        $this->apiClient = $apiClient;
    }

    public function process(Event $event): void
    {
        try {
            $this->apiClient->trackEvent(
                $event->getType(),
                $event->getUuid(),
                $event->getProductId(),
                $event->getCategoryId(),
                $event->getPrice()
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw $e;
        }
    }
}