<?php

namespace Omega\Cyberkonsultant\Mapper;

use Cyberkonsultant\Builder\Feature\ChoiceBuilder;
use Cyberkonsultant\Builder\FeatureBuilder;
use Cyberkonsultant\DTO\Feature;
use Cyberkonsultant\DTO\Feature\Choice;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Api\Data\AttributeOptionInterface;

class AttributeMapper
{
    /**
     * @param ProductAttributeInterface[] $mageAttributes
     * @return Feature[]
     */
    public function mapToCKFeatures(array $mageAttributes)
    {
        $features = [];

        foreach ($mageAttributes as $attribute) {
            if (!$attribute->getIsUserDefined()) {
                continue;
            }

            if (empty($attribute->getOptions())) {
                continue;
            }

            $featureBuilder = new FeatureBuilder();
            $featureBuilder->setId($attribute->getAttributeId())->setName($attribute->getDefaultFrontendLabel());
            $choices = $this->mapOptionsToChoices($attribute->getOptions());

            foreach ($choices as $choice) {
                $featureBuilder->addChoice($choice);
            }

            $features[] = $featureBuilder->getResult();
        }

        return $features;
    }

    /**
     * @param AttributeOptionInterface[] $options
     * @return Choice[]
     */
    public function mapOptionsToChoices(array $options)
    {
        $choices = [];

        foreach ($options as $option) {
            $choices[] = (new ChoiceBuilder())->setId($option->getValue())->setName($option->getLabel())->getResult();
        }

        return $choices;
    }
}
