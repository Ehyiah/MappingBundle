<?php

namespace Ehyiah\MappingBundle\Transformer;

use DateTime;
use DateTimeInterface;
use Ehyiah\MappingBundle\Exceptions\TransformeException;
use Ehyiah\MappingBundle\Transformer\Interfaces\TransformerInterface;
use Exception;

final class DateTimeTransformer implements TransformerInterface
{
    public function transformationSupports(): string
    {
        return self::class;
    }

    /**
     * @param array<mixed> $options
     *
     * @throws Exception
     */
    public function transform(mixed $data, array $options, object $targetObject, object $mappedObject): DateTime|string|null
    {
        if (null === $data) {
            return null;
        }

        return $this->executeTransformation($data, $options);
    }

    /**
     * @param array<mixed> $options
     *
     * @throws Exception
     */
    public function reverseTransform(mixed $data, array $options, object $targetObject, object $mappedObject): DateTime|string|null
    {
        if (null === $data) {
            return null;
        }

        return $this->executeTransformation($data, $options);
    }

    /**
     * @param array<mixed> $options
     *
     * @throws Exception
     */
    private function executeTransformation(mixed $data, array $options): DateTime|string
    {
        if ($data instanceof DateTimeInterface) {
            if (isset($options['format'])) {
                return $data->format($options['format']);
            }

            return $data->format('Y/m/d');
        }

        if (is_string($data)) {
            return new DateTime($data);
        }

        throw new TransformeException('Problem while transforming with . ' . self::class);
    }
}
