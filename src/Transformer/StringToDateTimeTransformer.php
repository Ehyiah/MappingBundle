<?php

namespace Ehyiah\MappingBundle\Transformer;

use DateTime;
use DateTimeInterface;
use Ehyiah\MappingBundle\Exceptions\WrongDataTypeTransformerException;
use Ehyiah\MappingBundle\Transformer\Interfaces\TransformerInterface;
use Exception;

final class StringToDateTimeTransformer implements TransformerInterface
{
    public function transformationSupports(): string
    {
        return self::class;
    }

    /**
     * @param array<mixed> $options
     *
     * @throws WrongDataTypeTransformerException
     * @throws Exception
     */
    public function transform(mixed $data, array $options, object $targetObject, object $mappedObject): ?DateTime
    {
        if (null === $data) {
            return null;
        }

        if (!is_string($data)) {
            throw new WrongDataTypeTransformerException('Data is supposed to be a string to use transform : ' . self::class . ' ' . gettype($data) . ' provided');
        }

        return new DateTime($data);
    }

    /**
     * @param array<mixed> $options
     *
     * @throws WrongDataTypeTransformerException
     */
    public function reverseTransform(mixed $data, array $options, object $targetObject, object $mappedObject): ?string
    {
        if (null === $data) {
            return null;
        }

        if (!$data instanceof DateTimeInterface) {
            throw new WrongDataTypeTransformerException('Data is supposed to be a DateTime Interface to use reverse Transformer : ' . self::class . ' ' . gettype($data) . ' provided');
        }

        if (isset($options['format'])) {
            return $data->format($options['format']);
        }

        return $data->format('Y/m/d');
    }
}
