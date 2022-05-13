<?php

namespace Omega\Cyberkonsultant\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Omega\Cyberkonsultant\Helper\Configuration;

class Recurring implements InstallSchemaInterface
{
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (!$this->configuration->getGeneralConfig('installation_date')) {
            $this->configuration->setGeneralConfig(
                'installation_date',
                (new \DateTime())->format(\DateTimeInterface::ATOM)
            );
        }
    }
}
