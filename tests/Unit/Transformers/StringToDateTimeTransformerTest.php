<?php

namespace Ehyiah\MappingBundle\Tests\Unit\Transformers;

use DateTime;
use DateTimeInterface;
use Ehyiah\MappingBundle\Tests\Dummy\DummyMappedObject;
use Ehyiah\MappingBundle\Tests\Dummy\DummyTargetObject;
use Ehyiah\MappingBundle\Transformer\StringToDateTimeTransformer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @coversDefaultClass \Ehyiah\MappingBundle\Transformer\StringToDateTimeTransformer
 */
final class StringToDateTimeTransformerTest extends KernelTestCase
{
    /**
     * @covers ::transform
     */
    public function testTransform(): void
    {
        $transformer = new StringToDateTimeTransformer();

        $data = '2025-12-12';

        $result = $transformer->transform($data, [], new DummyTargetObject(), new DummyMappedObject());

        $this->assertInstanceOf(DateTimeInterface::class, $result);
    }

    /**
     * @covers ::reverseTransform
     */
    public function testReverseTransform(): void
    {
        $transformer = new StringToDateTimeTransformer();

        $data = new DateTime('2025-12-12');

        $result = $transformer->reverseTransform($data, [], new DummyTargetObject(), new DummyMappedObject());

        $this->assertEquals('2025/12/12', $result);
    }

    /**
     * @covers ::reverseTransform
     */
    public function testReverseTransformWithFormat(): void
    {
        $transformer = new StringToDateTimeTransformer();

        $data = new DateTime('2025/12/12');

        $result = $transformer->reverseTransform($data, ['format' => 'Y-m-d'], new DummyTargetObject(), new DummyMappedObject());

        $this->assertEquals('2025-12-12', $result);
    }
}
