<?php

namespace Ehyiah\MappingBundle\Transformer;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('ehyiah.mapping_bundle.transformer')]
interface TransformerInterface
{
    public function transformationSupports(): string;

    /**
     * @param array<mixed> $options
     */
    public function transform(mixed $data, array $options, object $entity, object $dto): mixed;

    /**
     * @param array<mixed> $options
     */
    public function reverseTransform(mixed $data, array $options, object $entity, object $dto): mixed;
}
