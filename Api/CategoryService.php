<?php
namespace Omega\Cyberkonsultant\Api;

use Omega\Cyberkonsultant\Api\Traits\NormalizeTrait;

class CategoryService extends AbstractApiService
{
    use NormalizeTrait;

    public function getList()
    {
        $this->checkPermission();

        $categories = $this->categoriesProvider->getCategories();
        $categories = $this->categoryMapper->mapToCKCategories($categories->getItems());

        return $this->jsonResponse([
            'data' => $this->normalize($categories),
        ]);
    }
}
