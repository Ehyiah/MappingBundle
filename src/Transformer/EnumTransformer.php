<?php

namespace Ehyiah\MappingBundle\Transformer;

use Ehyiah\MappingBundle\Exceptions\ReverseTransformeException;
use Ehyiah\MappingBundle\Transformer\Interfaces\TransformerInterface;

final class EnumTransformer implements TransformerInterface
{
    public function transformationSupports(): string
    {
        return self::class;
    }

    /**
     * @throws ReverseTransformeException
     */
    public function transform(mixed $data, array $options, object $targetObject, object $mappedObject): mixed
    {
        if (null === $data) {
            return null;
        }

        return $this->executeTransformation($data, $options);
    }

    /**
     * @throws ReverseTransformeException
     */
    public function reverseTransform(mixed $data, array $options, object $targetObject, object $mappedObject): mixed
    {
        if (null === $data) {
            return null;
        }

        return $this->executeTransformation($data, $options);
    }

    /**
     * @param array<string, string> $options
     *
     * @throws ReverseTransformeException
     */
    private function executeTransformation(mixed $data, array $options): mixed
    {
        if (!isset($options['enum'])) {
            throw new ReverseTransformeException('option enum must be specified to use this reverse transformer : ' . self::class);
        }

        $enumClass = $options['enum'];
        if (!class_exists($enumClass)) {
            throw new ReverseTransformeException('enum class doest not exist : ' . $enumClass);
        }

        if (is_array($data)) {
            if (is_string($data[0])) {
                return array_map(fn ($item) => $enumClass::tryFrom($item), $data);
            }

            $array = [];

            foreach ($data as $datum) {
                $array[] = $datum->value;
            }

            return $array;
        }

        if (is_string($data)) {
            return $enumClass::tryFrom($data);
        }

        return $data->value;
    }
}
