<?php

namespace Omega\Cyberkonsultant\Provider;

use Magento\Catalog\Api\CategoryListInterface;
use Magento\Catalog\Api\Data\CategorySearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

class CategoriesProvider
{
    /**
     * @var CategoryListInterface
     */
    private $categoryList;

    /**
     * @var SearchCriteriaInterface
     */
    private $criteria;

    public function __construct(CategoryListInterface $categoryList, SearchCriteriaInterface $criteria)
    {
        $this->categoryList = $categoryList;
        $this->criteria = $criteria;
    }

    /**
     * @return CategorySearchResultsInterface
     */
    public function getCategories()
    {
        return $this->categoryList->getList($this->criteria);
    }
}
