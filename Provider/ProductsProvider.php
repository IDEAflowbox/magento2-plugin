<?php

namespace Omega\Cyberkonsultant\Provider;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Model\Product\Attribute\Source\Status;

class ProductsProvider
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteria;

    /**
     * @var FilterGroup
     */
    private $filterGroup;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var Status
     */
    private $productStatus;

    /**
     * @var Visibility
     */
    private $productVisibility;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $criteria,
        FilterGroup $filterGroup,
        FilterBuilder $filterBuilder,
        Status $productStatus,
        Visibility $productVisibility
    ) {
        $this->productRepository = $productRepository;
        $this->criteria = $criteria;
        $this->filterGroup = $filterGroup;
        $this->filterBuilder = $filterBuilder;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
    }

    /**
     * @return ProductSearchResultsInterface
     * @throws NoSuchEntityException
     */
    public function getProductData($currentPage = 1, $pageSize = 50)
    {
        $filterGroup = $this->filterGroup->setFilters([
            $this->filterBuilder
                ->setField(ProductInterface::STATUS)
                ->setConditionType('in')
                ->setValue($this->productStatus->getVisibleStatusIds())
                ->create(),
            $this->filterBuilder
                ->setField(ProductInterface::VISIBILITY)
                ->setConditionType('in')
                ->setValue($this->productVisibility->getVisibleInSiteIds())
                ->create(),
        ]);

        $criteria = $this->criteria
            ->setFilterGroups([$filterGroup])
            ->setCurrentPage($currentPage)
            ->setPageSize($pageSize);
        return $this->productRepository->getList($criteria->create());
    }
}
