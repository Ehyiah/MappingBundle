<?php

namespace Ehyiah\MappingBundle\Transformer;

use Ehyiah\MappingBundle\Exceptions\ReverseTransformeException;
use Ehyiah\MappingBundle\Exceptions\WrongDataTypeTransformerException;
use Ehyiah\MappingBundle\Transformer\Interfaces\ReverseTransformerInterface;
use Ehyiah\MappingBundle\Transformer\Interfaces\TransformerInterface;

final class EnumTransformer implements TransformerInterface, ReverseTransformerInterface
{
    public function transformationSupports(): string
    {
        return self::class;
    }

    public function transform(mixed $data, array $options, object $entity, object $dto): mixed
    {
        return $data;
    }

    /**
     * @param array<mixed> $options
     *
     * @throws WrongDataTypeTransformerException
     * @throws ReverseTransformeException
     */
    public function reverseTransform(mixed $data, array $options, object $entity, object $dto): mixed
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
