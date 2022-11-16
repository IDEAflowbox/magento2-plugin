<?php

namespace Omega\Cyberkonsultant\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Omega\Cyberkonsultant\Helper\Configuration;

class MigrateEvents extends Field
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(Context $context, Configuration $configuration, array $data = [])
    {
        parent::__construct($context, $data);
        $this->configuration = $configuration;
    }

    /**
     * Set template to itself
     *
     * @return $this
     * @since 100.1.0
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Omega_Cyberkonsultant::system/config/migrate_events.phtml');
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param AbstractElement $element
     * @return string
     * @since 100.1.0
     */
    public function render(AbstractElement $element)
    {
        $element = clone $element;
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param AbstractElement $element
     * @return string
     * @since 100.1.0
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        if ($this->configuration->getGeneralConfig('data_migrated')) {
            return '';
        }

        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('cyberkonsultant/system_config/migrateevents'),
            ]
        );

        return $this->_toHtml();
    }
}
