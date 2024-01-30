<?php

namespace Ehyiah\MappingBundle\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class MappingAware
{
    /**
     * If the attribute is on class, then 'target' parameter must be an entity.
     * If the attribute is on property, then 'target' must be an entity property
     * If the attribute is the same on both DTO and entity, no target is needed the service will automap on the same name for entity and DTO
     *
     * Transformers can be applied when mapping property to Entity or DTO
     * transform property is to be used when mapping to Entity
     * reverseTransform is to be used when mapping to DTO
     * options is an array passed on transform and reverseTransform methods if you need to add context or custom options to use in your transformers
     *
     * Custom transformers can easily be created : look at DateTransformer as example to create your own as needed
     */
    public function __construct(
        public ?string $target = null,
        public ?string $transform = null,
        public ?string $reverseTransform = null,
        /** @var array<mixed>|null */
        public ?array $options = null,
    ) {
    }
}
