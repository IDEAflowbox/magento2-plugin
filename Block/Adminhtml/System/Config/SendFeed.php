<?php

namespace Omega\Cyberkonsultant\Block\Adminhtml\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

class SendFeed extends Field
{
    /**
     * Set template to itself
     *
     * @return $this
     * @since 100.1.0
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->setTemplate('Omega_Cyberkonsultant::system/config/send_feed.phtml');
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
        $originalData = $element->getOriginalData();
        $this->addData(
            [
                'button_label' => __($originalData['button_label']),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('cyberkonsultant/system_config/sendfeed'),
            ]
        );

        return $this->_toHtml();
    }
}
