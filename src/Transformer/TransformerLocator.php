<?php

namespace Ehyiah\MappingBundle\Transformer;

use Ehyiah\MappingBundle\Exceptions\MultipleReverseTransformerException;
use Ehyiah\MappingBundle\Exceptions\MultipleTransformerException;
use Ehyiah\MappingBundle\Exceptions\ReverseTransformerNotFoundException;
use Ehyiah\MappingBundle\Exceptions\TransformerNotFoundException;
use Ehyiah\MappingBundle\Transformer\Interfaces\ReverseTransformerInterface;
use Ehyiah\MappingBundle\Transformer\Interfaces\TransformerInterface;

final class TransformerLocator
{
    /**
     * @var array<TransformerInterface>
     */
    private array $transformers = [];

    /**
     * @var array<ReverseTransformerInterface>
     */
    private array $reverseTransformers = [];

    public function addTransformer(TransformerInterface $transformer): void
    {
        $this->transformers[] = $transformer;
    }

    public function addReverseTransformer(ReverseTransformerInterface $reverseTransformer): void
    {
        $this->reverseTransformers[] = $reverseTransformer;
    }

    /**
     * @throws MultipleTransformerException
     * @throws TransformerNotFoundException
     */
    public function returnTransformer(string $transformation): TransformerInterface
    {
        $transformerNames = array_filter($this->transformers, function ($value) use ($transformation): bool {
            return $value->transformationSupports() === $transformation;
        });

        if (0 === count($transformerNames)) {
            throw new TransformerNotFoundException('No transformer found for transformation : ' . $transformation);
        }
        if (count($transformerNames) > 1) {
            throw new MultipleTransformerException('More than one transformer found for : ' . $transformation);
        }

        return array_shift($transformerNames);
    }

    /**
     * @throws MultipleReverseTransformerException
     * @throws ReverseTransformerNotFoundException
     */
    public function returnReverseTransformer(string $transformation): ReverseTransformerInterface
    {
        $reverseTransformerNames = array_filter($this->reverseTransformers, function ($value) use ($transformation): bool {
            return $value->transformationSupports() === $transformation;
        });

        if (0 === count($reverseTransformerNames)) {
            throw new ReverseTransformerNotFoundException('No reverse transformer found for transformation : ' . $transformation);
        }
        if (count($reverseTransformerNames) > 1) {
            throw new MultipleReverseTransformerException('More than one reverse transformer found for : ' . $transformation);
        }

        return array_shift($reverseTransformerNames);
    }
}
