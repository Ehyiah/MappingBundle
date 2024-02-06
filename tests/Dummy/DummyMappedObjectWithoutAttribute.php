<?php

namespace Ehyiah\MappingBundle\Tests\Dummy;

use Ehyiah\MappingBundle\Attributes\MappingAware;

class DummyMappedObjectWithoutAttribute
{
    #[MappingAware]
    public string $string;
}
