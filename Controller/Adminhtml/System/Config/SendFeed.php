<?php

namespace Omega\Cyberkonsultant\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\MessageQueue\PublisherInterface;
use Psr\Log\LoggerInterface;

class SendFeed extends Action implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    public function __construct(
        Context            $context,
        JsonFactory        $jsonFactory,
        LoggerInterface    $logger,
        PublisherInterface $publisher
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->publisher = $publisher;
    }

    public function execute()
    {
        $result = [
            'success' => false,
            'errorMessage' => '',
        ];

        try {
            $this->publisher->publish('cyberkonsultant.send.feed', '');
            $result['success'] = true;
        } catch (\Exception $e) {
            $this->logger->error($e);
            $result['errorMessage'] = $e->getMessage();
        }

        return $this->jsonFactory->create()->setData($result);
    }
}
