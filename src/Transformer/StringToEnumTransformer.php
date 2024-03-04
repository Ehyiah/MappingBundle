<?php

namespace Ehyiah\MappingBundle\Transformer;

use Ehyiah\MappingBundle\Exceptions\ReverseTransformeException;
use Ehyiah\MappingBundle\Exceptions\WrongDataTypeTransformerException;
use Ehyiah\MappingBundle\Transformer\Interfaces\TransformerInterface;

final class StringToEnumTransformer implements TransformerInterface
{
    public function transformationSupports(): string
    {
        return self::class;
    }

    public function transform(mixed $data, array $options, object $targetObject, object $mappedObject): mixed
    {
        return $data;
    }

    /**
     * @param array<mixed> $options
     *
     * @throws WrongDataTypeTransformerException
     * @throws ReverseTransformeException
     */
    public function reverseTransform(mixed $data, array $options, object $targetObject, object $mappedObject): mixed
    {
        if (null === $data) {
            return [];
        }
        if (!isset($options['enum'])) {
            throw new ReverseTransformeException('option enum must be specified to use this reverse transformer : ' . self::class);
        }

        $enumClass = $options['enum'];
        if (!class_exists($enumClass)) {
            throw new ReverseTransformeException('enum class doest not exist : ' . $enumClass);
        }

        if (is_array($data)) {
            return array_map(fn ($item) => $enumClass::tryFrom($item), $data);
        }

        return $enumClass::tryFrom($data);
    }
}
