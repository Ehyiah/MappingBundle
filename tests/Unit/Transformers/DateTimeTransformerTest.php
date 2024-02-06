<?php

namespace Ehyiah\MappingBundle\Tests\Unit\Transformers;

use Ehyiah\MappingBundle\Tests\Dummy\DummyMappedObject;
use Ehyiah\MappingBundle\Tests\Dummy\DummyTargetObject;
use Ehyiah\MappingBundle\Transformer\DateTimeTransformer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @coversDefaultClass \Ehyiah\MappingBundle\Transformer\DateTimeTransformer
 */
final class DateTimeTransformerTest extends KernelTestCase
{
    /**
     * @covers ::transform
     */
    public function testTransform(): void
    {
        $transformer = new DateTimeTransformer();

        $data = '2025-12-12';

        $result = $transformer->transform($data, [], new DummyTargetObject(), new DummyMappedObject());

        $this->assertInstanceOf(\DateTimeInterface::class, $result);
    }

    /**
     * @covers ::reverseTransform
     */
    public function testReverseTransform(): void
    {
        $transformer = new DateTimeTransformer();

        $data = new \DateTime('2025-12-12');

        $result = $transformer->reverseTransform($data, [], new DummyTargetObject(), new DummyMappedObject());

        $this->assertEquals('2025/12/12', $result);
    }
}
