<?php

namespace Ehyiah\MappingBundle\Tests\Dummy;

class DummyTargetObject
{
    public string $string;
    public bool $boolean;
    public ?string $notMappedProperty = null;
    public ?string $withTransform = null;
    public ?string $withReverseTransform = null;
    public ?string $theOtherDestination = null;
}
