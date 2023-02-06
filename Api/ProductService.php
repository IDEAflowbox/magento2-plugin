<?php
namespace Omega\Cyberkonsultant\Api;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Visibility;
use Omega\Cyberkonsultant\Api\Traits\NormalizeTrait;

class ProductService extends AbstractApiService
{
    use NormalizeTrait;

    /**
     * @return null
     * @throws \ReflectionException
     */
    public function getList()
    {
        $this->checkPermission();

        $currentPage = (int) $this->getRequest()->get('page', 1);
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter(ProductInterface::VISIBILITY, Visibility::VISIBILITY_BOTH);
        $collection->addAttributeToFilter(ProductInterface::STATUS, 1);
        $collection->setPageSize(50);
        $collection->setCurPage($currentPage);

        $data = [];
        if ($currentPage <= $collection->getLastPageNumber()) {
            $products = $this->productMapper->mapToCKProducts($collection);
            $data = array_map(function ($product) {
                if (isset($product['categories'])) {
                    $product['categories'] = array_map(function ($category) {
                        return $category['id'];
                    }, $product['categories']);
                } else {
                    $product['categories'] = [];
                }

                return $product;
            }, $this->normalize($products));
        }

        return $this->jsonResponse([
            'page' => $currentPage,
            'data' => $data,
        ]);
    }
}
