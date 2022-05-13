<?php

namespace Omega\Cyberkonsultant\Helper;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Configuration extends AbstractHelper
{
    const CONFIG_PREFIX = 'cyberkonsultant/';

    /**
     * @var WriterInterface
     */
    private $writer;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    public function __construct(Context $context, WriterInterface $writer, TypeListInterface $cacheTypeList)
    {
        parent::__construct($context);
        $this->writer = $writer;
        $this->cacheTypeList = $cacheTypeList;
    }

    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::CONFIG_PREFIX .'general/'. $code, $storeId);
    }

    public function setGeneralConfig($code, $value)
    {
        $this->setConfigValue(self::CONFIG_PREFIX .'general/'. $code, $value);
    }

    private function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    private function setConfigValue($field, $value)
    {
        $this->writer->save($field, $value);
        $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
        $this->cacheTypeList->cleanType(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);
    }
}
