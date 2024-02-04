<?php

namespace Ehyiah\MappingBundle\Transformer;

use DateTime;
use DateTimeInterface;
use Ehyiah\MappingBundle\Exceptions\WrongDataTypeTransformerException;
use Ehyiah\MappingBundle\Transformer\Interfaces\ReverseTransformerInterface;
use Ehyiah\MappingBundle\Transformer\Interfaces\TransformerInterface;
use Exception;

final class DateTimeTransformer implements TransformerInterface, ReverseTransformerInterface
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
    public function transform(mixed $data, array $options, object $entity, object $dto): DateTime
    {
        if (!is_string($data)) {
            throw new WrongDataTypeTransformerException('Data is supposed to be a string to use transforme : ' . self::class . ' transformer, ' . gettype($data) . ' provided');
        }

        return new DateTime($data);
    }

    /**
     * @param array<mixed> $options
     *
     * @throws WrongDataTypeTransformerException
     */
    public function reverseTransform(mixed $data, array $options, object $entity, object $dto): string
    {
        if (!$data instanceof DateTimeInterface) {
            throw new WrongDataTypeTransformerException('Data is supposed to be a DateTime Interface to use reverse Transformer : ' . self::class . ' transformer, ' . gettype($data) . ' provided');
        }

        return $data->format('Y/m/d');
    }
}
