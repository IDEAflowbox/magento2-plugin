<?php

namespace Omega\Cyberkonsultant\Provider;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

class AttributesProvider
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var SearchCriteriaInterface
     */
    private $criteria;

    public function __construct(ProductAttributeRepositoryInterface $attributeRepository, SearchCriteriaInterface $criteria)
    {
        $this->attributeRepository = $attributeRepository;
        $this->criteria = $criteria;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductAttributeSearchResultsInterface
     */
    public function getAttributes()
    {
        // TODO: GET ONLY with is_user_defined = 1 ?
        return $this->attributeRepository->getList($this->criteria);
    }
}
