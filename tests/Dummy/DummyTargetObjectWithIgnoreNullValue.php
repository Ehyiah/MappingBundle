<?php

namespace Ehyiah\MappingBundle\Tests\Dummy;

use DateTime;

class DummyTargetObjectWithIgnoreNullValue
{
    public ?string $nullableString = 'not null';
    public string $string;
    public bool $boolean;
    public ?string $notMappedProperty = null;
    public ?DateTime $date = null;
    public ?string $theOtherDestination = null;
}
