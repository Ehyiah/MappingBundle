<?php

namespace Ehyiah\MappingBundle\Transformer\Interfaces;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('ehyiah.mapping_bundle.reverse_transformer')]
interface ReverseTransformerInterface
{
    /**
     * simply return reverseTransformer class FQCN
     */
    public function transformationSupports(): string;

    /**
     * @param array<mixed> $options
     */
    public function reverseTransform(mixed $data, array $options, object $entity, object $dto): mixed;
}
