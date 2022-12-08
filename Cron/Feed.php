<?php

namespace Omega\Cyberkonsultant\Cron;

use Omega\Cyberkonsultant\Consumer\SendFeedConsumer;

class Feed
{
    private $sendFeedConsumer;

    public function __construct(SendFeedConsumer $sendFeedConsumer)
    {
        $this->sendFeedConsumer = $sendFeedConsumer;
    }

    public function execute()
    {
        $this->sendFeedConsumer->process('');
    }
}
