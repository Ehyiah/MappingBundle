<?php

namespace Ehyiah\MappingBundle\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class MappingAware
{
    /**
     * If the attribute is on class, then 'target' parameter must be an entity.
     * If the attribute is on property, then 'target' must be an entity property
     * If the attribute is the same on both DTO and entity, no target is needed the service will auto-map on the same field name for entity and DTO
     * If you want to ignore properties with a null value, use the 'ignoreNullValue' on that property and the null value will not be mapped
     *
     * Transformers can be applied when mapping property to Entity or DTO
     * transformer property is to be used when mapping to Entity
     * options is an array passed on transformer and reverseTransformer methods if you need to add context or custom options to use in your transformers
     *
     * Custom transformers can easily be created : look at DateTransformer as example to create your own as needed
     */
    public function __construct(
        public ?string $target = null,
        public ?string $transformer = null,
        /** @var array<mixed> */
        public array $options = [],
        public bool $ignoreNullValue = false,
    ) {
    }
}
