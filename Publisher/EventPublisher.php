<?php
declare(strict_types=1);

namespace Omega\Cyberkonsultant\Publisher;

use Omega\Cyberkonsultant\Messenger\Dispatcher;
use Omega\Cyberkonsultant\Messenger\Queue;
use Omega\Cyberkonsultant\ValueObject\Event;

class EventPublisher
{
    private $dispatcher;

    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function publish(Event $event): void
    {
        $this->dispatcher->dispatch(new Queue($event->getDto(), 'events'));
    }
}