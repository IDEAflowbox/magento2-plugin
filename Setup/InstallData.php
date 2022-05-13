<?php
declare(strict_types=1);

namespace Omega\Cyberkonsultant\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Omega\Cyberkonsultant\Helper\Configuration;

class InstallData implements InstallDataInterface
{
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->configuration->setGeneralConfig(
            'installation_date',
            (new \DateTime())->format(\DateTimeInterface::ATOM)
        );
    }
}
