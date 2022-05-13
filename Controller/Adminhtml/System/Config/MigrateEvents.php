<?php

namespace Omega\Cyberkonsultant\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\MessageQueue\PublisherInterface;
use Omega\Cyberkonsultant\Helper\Configuration;
use Psr\Log\LoggerInterface;

class MigrateEvents extends Action implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    private $jsonFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PublisherInterface
     */
    private $publisher;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(
        Context            $context,
        JsonFactory        $jsonFactory,
        LoggerInterface    $logger,
        PublisherInterface $publisher,
        Configuration      $configuration
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        $this->publisher = $publisher;
        $this->configuration = $configuration;
    }

    public function execute()
    {
        $result = [
            'success' => false,
            'errorMessage' => '',
        ];

        if ($this->configuration->getGeneralConfig('data_migrated')) {
            $result['success'] = true;
            return $this->jsonFactory->create()->setData($result);
        }

        try {
            $this->publisher->publish('cyberkonsultant.migrate.events', '');
            $this->configuration->setGeneralConfig('data_migrated', 1);
            $result['success'] = true;
        } catch (\Exception $e) {
            $this->logger->error($e);
            $result['errorMessage'] = $e->getMessage();
        }

        return $this->jsonFactory->create()->setData($result);
    }
}
