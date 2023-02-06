<?php
namespace Omega\Cyberkonsultant\Api;

use Omega\Cyberkonsultant\Api\Traits\NormalizeTrait;

class FeatureService extends AbstractApiService
{
    use NormalizeTrait;

    /**
     * @return null
     * @throws \ReflectionException
     */
    public function getList()
    {
        $this->checkPermission();

        $attributes = $this->attributesProvider->getAttributes();
        $features = $this->attributeMapper->mapToCKFeatures($attributes->getItems());

        return $this->jsonResponse([
            'data' => $this->normalize($features),
        ]);
    }
}
