<?php
namespace Omega\Cyberkonsultant\Config\Source;


use Magento\Store\Model\StoreManagerInterface;

class StoreId implements \Magento\Framework\Data\OptionSourceInterface
{
    private $storeManager;
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $storeManagerDataList = $this->storeManager->getStores();
        $options = [];

        foreach ($storeManagerDataList as $key => $value) {
            $options[] = ['label' => $key.'. '.$value['name'].' - '.$value['code'], 'value' => $key];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $storeManagerDataList = $this->storeManager->getStores();
        $options = [];

        foreach ($storeManagerDataList as $key => $value) {
            $options[] = $key;
        }
        return $options;
    }
}
