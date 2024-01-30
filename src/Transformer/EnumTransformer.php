<?php

namespace Ehyiah\MappingBundle\Transformer;

use Ehyiah\MappingBundle\Exceptions\ReverseTransformeException;
use Ehyiah\MappingBundle\Exceptions\WrongDataTypeTransformerException;

final class EnumTransformer implements TransformerInterface
{
    public const TRANSFORMATION_NAME = 'EnumTransformer';

    public function transformationSupports(): string
    {
        return self::TRANSFORMATION_NAME;
    }

    public function transform(mixed $data, array $options = null): mixed
    {
        return $data;
    }

    /**
     * @throws WrongDataTypeTransformerException
     * @throws ReverseTransformeException
     */
    public function reverseTransform(mixed $data, array $options = null): mixed
    {
        if (null === $data) {
            return [];
        }
        if (!isset($options['enum'])) {
            throw new ReverseTransformeException('option enum must be specified to use this transformer : ' . self::class);
        }

        $enumClass = $options['enum'];
        if (!class_exists($enumClass)) {
            throw new ReverseTransformeException('enum class doest not exist : ' . $enumClass);
        }

        if (!is_array($data)) {
            throw new WrongDataTypeTransformerException('Data is supposed to be an array in reverseTransform inside ' . self::class);
        }

        return array_map(fn ($item) => $enumClass::tryFrom($item), $data);
    }
}
