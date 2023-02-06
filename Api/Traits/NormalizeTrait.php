<?php
namespace Omega\Cyberkonsultant\Api\Traits;


trait NormalizeTrait
{
    /**
     * @param $object
     * @param $excludeFields
     * @return array
     * @throws \ReflectionException
     */
	protected function normalize($object, $excludeFields = []): array
	{
		$data = [];

		if (is_array($object)) {
			foreach ($object as $item) {
				if (is_object($item) || is_array($item)) {
					$data[] = $this->normalize($item, $excludeFields);
				} else {
					$data[] = $item;
				}
			}
		} else {
			$reflection = new \ReflectionClass($object);
			foreach ($reflection->getMethods() as $method) {
				if (preg_match('/(get|is)([A-Z]{1}.*)/', $method->getName(), $m)) {
					$originalProperty = lcfirst(preg_replace('/(get|is)([A-Z]{1}.*)/', '$2', $method->getName()));

					if (!in_array($originalProperty, $excludeFields)) {
						$methodName = $method->getName();
						$propertyName = lcfirst($m[2]);

						$value = $object->$methodName();
						$nextValue = null;


						if ($value instanceof \DateTime) {
							$nextValue = $value->format(\DateTime::ATOM);
						} else if (is_object($value) || is_array($value)) {
							$nextValue = $this->normalize($value, $excludeFields);
						} else {
							$nextValue = $value;
						}

						$snakedPropertyName = strtolower(preg_replace("/[A-Z]/", "_$0", lcfirst($propertyName)));
						$data[$snakedPropertyName] = $nextValue;
					}
				}
			}
		}

		return $data;
	}
}