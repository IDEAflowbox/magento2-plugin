<?php

namespace Omega\Cyberkonsultant\Provider;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Omega\Cyberkonsultant\Helper\Configuration;

class OrderProvider
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaInterface
     */
    private $criteria;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaInterface  $criteria,
        Configuration            $configuration
    ) {
        $this->orderRepository = $orderRepository;
        $this->criteria = $criteria;
        $this->configuration = $configuration;
    }

    public function getOrders($page = 1, $limit = 20): OrderSearchResultInterface
    {
        $this->criteria->setPageSize($limit);
        $this->criteria->setCurrentPage($page);
        $filter = new Filter();
        $filter->setField('migrated_to_ck_at')->setConditionType('null');
        $createdAtFilter = new Filter();
        $createdAtFilter->setField('created_at')->setConditionType('to')->setValue(
            new \DateTime($this->configuration->getGeneralConfig('installation_date'))
        );
        $this->criteria->setFilterGroups([(new FilterGroup())->setFilters([$filter]), (new FilterGroup())->setFilters([$createdAtFilter])]);
        return $this->orderRepository->getList($this->criteria);
    }
}
