<?php

namespace Ehyiah\MappingBundle\Transformer\Interfaces;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('ehyiah.mapping_bundle.transformer')]
interface TransformerInterface
{
    /**
     * simply return transformer class FQCN
     */
    public function transformationSupports(): string;

    /**
     * @param array<mixed> $options
     */
    public function transform(mixed $data, array $options, object $targetObject, object $mappedObject): mixed;
}
