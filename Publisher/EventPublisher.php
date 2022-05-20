<?php
declare(strict_types=1);

namespace Omega\Cyberkonsultant\Publisher;


use Magento\Framework\MessageQueue\PublisherInterface;
use Omega\Cyberkonsultant\ValueObject\Event;

class EventPublisher
{
    private const TOPIC_NAME = 'cyberkonsultant.track.events';

    private $publisher;

    public function __construct(PublisherInterface $publisher)
    {
        $this->publisher = $publisher;
    }

    public function publish(Event $event): void
    {
        $this->publisher->publish(self::TOPIC_NAME, $event);
    }
}