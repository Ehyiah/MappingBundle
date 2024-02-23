<?php

namespace Ehyiah\MappingBundle\DependencyInjection;

use Ehyiah\MappingBundle\Exceptions\MultipleTransformerException;
use Ehyiah\MappingBundle\Exceptions\TransformerNotFoundException;
use Ehyiah\MappingBundle\Transformer\Interfaces\TransformerInterface;

class TransformerLocator
{
    /**
     * @var array<TransformerInterface>
     */
    private array $transformers = [];

    public function addTransformer(TransformerInterface $transformer): void
    {
        $this->transformers[] = $transformer;
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
}
