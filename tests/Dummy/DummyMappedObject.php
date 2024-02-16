<?php

namespace Ehyiah\MappingBundle\Tests\Dummy;

use DateTime;
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
    public ?string $withTransform = null;

    #[MappingAware(transformer: DateTimeTransformer::class, options: ['option1' => 'value1'])]
    public ?string $withTransformAndOptions = null;

    #[MappingAware(reverseTransformer: DateTimeTransformer::class)]
    public ?DateTime $withReverseTransform = null;

    #[MappingAware(reverseTransformer: DateTimeTransformer::class, options: ['option1' => 'value1'])]
    public ?DateTime $withReverseTransformAndOptions = null;

    #[MappingAware(target: 'theOtherDestination')]
    public ?string $withOtherDestination = null;
}
