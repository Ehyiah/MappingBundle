<?php

namespace Ehyiah\MappingBundle\Transformer;

use DateTime;
use DateTimeInterface;
use Ehyiah\MappingBundle\Exceptions\WrongDataTypeTransformerException;
use Exception;

final class DateTransformer implements TransformerInterface
{
    public const TRANSFORMATION_NAME = 'DateTime';

    public function transformationSupports(): string
    {
        return self::TRANSFORMATION_NAME;
    }

    /**
     * @throws WrongDataTypeTransformerException
     * @throws Exception
     */
    public function transform(mixed $data, array $options, object $entity, object $dto): DateTime
    {
        if (!is_string($data)) {
            throw new WrongDataTypeTransformerException('Data is supposed to be a string to use transforme : ' . self::TRANSFORMATION_NAME . ' transformer, ' . gettype($data) . ' provided');
        }

        return new DateTime($data);
    }

    /**
     * @throws WrongDataTypeTransformerException
     */
    public function reverseTransform(mixed $data, array $options, object $entity, object $dto): string
    {
        if (!$data instanceof DateTimeInterface) {
            throw new WrongDataTypeTransformerException('Data is supposed to be a DateTime Interface to use reverse Transformer : ' . self::TRANSFORMATION_NAME . ' transformer, ' . gettype($data) . ' provided');
        }

        return $data->format('Y/m/d');
    }
}
