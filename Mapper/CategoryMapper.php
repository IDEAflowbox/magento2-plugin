<?php

namespace Omega\Cyberkonsultant\Mapper;

use Cyberkonsultant\Builder\CategoryBuilder;
use Magento\Catalog\Api\Data\CategoryInterface;

class CategoryMapper
{
    /**
     * @param CategoryInterface[] $mageCategories
     * @return array
     */
    public function mapToCKCategories(array $mageCategories)
    {
        $categories = [];
        foreach ($mageCategories as $mageCategory) {
            $categoryBuilder = new CategoryBuilder();
            $categories[] = $categoryBuilder
                ->setId($mageCategory->getId())
                ->setName($mageCategory->getName())
                ->setImage($mageCategory->getImageUrl())
                ->setUrl($mageCategory->getUrl())
                ->getResult();
        }

        return $categories;
    }
}
