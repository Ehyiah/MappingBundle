<?php

namespace Ehyiah\MappingBundle\Tests\Unit\Transformers;

use Ehyiah\MappingBundle\Tests\Dummy\DummyEnum;
use Ehyiah\MappingBundle\Tests\Dummy\DummyMappedObject;
use Ehyiah\MappingBundle\Tests\Dummy\DummyTargetObject;
use Ehyiah\MappingBundle\Transformer\EnumTransformer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @coversDefaultClass \Ehyiah\MappingBundle\Transformer\EnumTransformer
 */
final class EnumTransformerTest extends KernelTestCase
{
    /**
     * @covers ::transform
     */
    public function testTransform(): void
    {
        $transformer = new EnumTransformer();

        $data = DummyEnum::ENUM_1;
        $result = $transformer->transform($data, ['enum' => DummyEnum::class], new DummyTargetObject(), new DummyMappedObject());
        $this->assertEquals('enum_value_1', $result);

        $data = [DummyEnum::ENUM_1, DummyEnum::ENUM_2];
        $result = $transformer->transform($data, ['enum' => DummyEnum::class], new DummyTargetObject(), new DummyMappedObject());
        $this->assertIsArray($result);
        $this->assertContains('enum_value_1', $result);
        $this->assertContains('enum_value_2', $result);

        $data = 'enum_value_2';
        $result = $transformer->transform($data, ['enum' => DummyEnum::class], new DummyTargetObject(), new DummyMappedObject());
        $this->assertInstanceOf(DummyEnum::class, $result);
        $this->assertEquals(DummyEnum::ENUM_2, $result);

        $data = ['enum_value_2', 'enum_value_1'];
        $result = $transformer->transform($data, ['enum' => DummyEnum::class], new DummyTargetObject(), new DummyMappedObject());
        $this->assertCount(2, $result);
        foreach ($result as $r) {
            $this->assertInstanceOf(DummyEnum::class, $r);
        }
    }

    /**
     * @covers ::reverseTransform
     */
    public function testReverseTransform(): void
    {
        $transformer = new EnumTransformer();

        $data = ['enum_value_2', 'enum_value_1'];
        $result = $transformer->reverseTransform($data, ['enum' => DummyEnum::class], new DummyTargetObject(), new DummyMappedObject());
        $this->assertCount(2, $result);
        foreach ($result as $r) {
            $this->assertInstanceOf(DummyEnum::class, $r);
        }

        $data = 'enum_value_2';
        $result = $transformer->reverseTransform($data, ['enum' => DummyEnum::class], new DummyTargetObject(), new DummyMappedObject());
        $this->assertInstanceOf(DummyEnum::class, $result);
        $this->assertEquals(DummyEnum::ENUM_2, $result);

        $data = 'invalid_value';
        $result = $transformer->reverseTransform($data, ['enum' => DummyEnum::class], new DummyTargetObject(), new DummyMappedObject());
        $this->assertNull($result);

        $data = DummyEnum::ENUM_1;
        $result = $transformer->reverseTransform($data, ['enum' => DummyEnum::class], new DummyTargetObject(), new DummyMappedObject());
        $this->assertEquals('enum_value_1', $result);

        $data = [DummyEnum::ENUM_1, DummyEnum::ENUM_2];
        $result = $transformer->reverseTransform($data, ['enum' => DummyEnum::class], new DummyTargetObject(), new DummyMappedObject());
        $this->assertIsArray($result);
        $this->assertContains('enum_value_1', $result);
        $this->assertContains('enum_value_2', $result);
    }
}
