<?php

namespace Omega\Cyberkonsultant\Model\Api;

use Magento\Framework\DataObject;

class PaginationResponse
{
    private $page;
    private $items = [];

    /**
     * @return int|null
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param int $page
     * @return $this
     */
    public function setPage(int $page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setItems(array $data)
    {
        $this->items = $data;
        return $this;
    }
}
