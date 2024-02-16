<?php

namespace Ehyiah\MappingBundle\Tests\Dummy;

use DateTime;

class DummyTargetObject
{
    public string $string;
    public bool $boolean;
    public ?string $notMappedProperty = null;
    public ?DateTime $withTransform = null;
    public ?string $withReverseTransform = null;
    public ?string $theOtherDestination = null;
}
