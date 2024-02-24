<?php

namespace Ehyiah\MappingBundle\Transformer;

use Ehyiah\MappingBundle\Exceptions\TransformeException;
use Ehyiah\MappingBundle\Transformer\Interfaces\TransformerInterface;
use Exception;

final class StringToBooleanTransformer implements TransformerInterface
{
    private string $defaultTrueValue = 'true';
    private string $defaultFalseValue = 'false';

    public function transformationSupports(): string
    {
        return self::class;
    }

    /**
     * @param array<mixed> $options
     *
     * @throws Exception
     */
    public function transform(mixed $data, array $options, object $targetObject, object $mappedObject): bool|null
    {
        if (null === $data) {
            return null;
        }

        if (isset($options['strict']) && true === $options['strict']) {
            $result = filter_var($data, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (!is_bool($result)) {
                throw new TransformeException('Can not transform to boolean value : ' . $data);
            }

            return $result;
        }

        return filter_var($data, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param array<mixed> $options
     *
     * @throws Exception
     */
    public function reverseTransform(mixed $data, array $options, object $targetObject, object $mappedObject): string|null
    {
        if (null === $data) {
            return null;
        }

        if (!isset($options['trueValue'])) {
            $trueValue = $this->defaultTrueValue;
        } else {
            $trueValue = $options['trueValue'];
        }
        if (!isset($options['falseValue'])) {
            $falseValue = $this->defaultFalseValue;
        } else {
            $falseValue = $options['falseValue'];
        }

        if (true === $data) {
            return $trueValue;
        }

        return $falseValue;
    }
}
