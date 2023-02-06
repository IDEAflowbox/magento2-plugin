<?php
declare(strict_types=1);

namespace Omega\Cyberkonsultant\Publisher;

use Magento\Framework\MessageQueue\PublisherInterface;
use Omega\Cyberkonsultant\ValueObject\PageEvent;

class PageEventPublisher
{
    private const TOPIC_NAME = 'cyberkonsultant.track.page_events';

    private $publisher;

    public function __construct(PublisherInterface $publisher)
    {
        $this->publisher = $publisher;
    }

    public function publish(PageEvent $event): void
    {
        $this->publisher->publish(self::TOPIC_NAME, $event);
    }
}