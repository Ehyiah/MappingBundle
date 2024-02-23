<?php

namespace Ehyiah\MappingBundle\Tests\Dummy;

use Ehyiah\MappingBundle\Attributes\MappingAware;
use Ehyiah\MappingBundle\Transformer\DateTimeTransformer;

#[MappingAware(target: DummyTargetObject::class)]
class DummyMappedObject
{
    #[MappingAware]
    public string $string;

    #[MappingAware]
    public bool $boolean;

    public ?string $notMappedProperty = 'i am not mapped';

    #[MappingAware(transformer: DateTimeTransformer::class)]
    public ?string $date = null;

    #[MappingAware(target: 'theOtherDestination')]
    public ?string $withOtherDestination = null;
}
