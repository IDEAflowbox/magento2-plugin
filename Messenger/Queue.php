<?php
declare(strict_types=1);

namespace Omega\Cyberkonsultant\Messenger;

class Queue
{
    /**
     * @var object
     */
    protected $body;

    /**
     * @var string
     */
    protected $queueName;

    /**
     * @param object $body
     * @param $queueName
     */
    public function __construct($body, $queueName)
    {
        $this->setBody($body);
        $this->setQueueName($queueName);
    }

    /**
     * @return object
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param object $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * @return mixed
     */
    public function getQueueName()
    {
        return $this->queueName;
    }

    /**
     * @param mixed $queueName
     */
    public function setQueueName($queueName)
    {
        $this->queueName = $queueName;
    }
}