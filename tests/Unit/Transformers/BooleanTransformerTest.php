<?php

namespace Ehyiah\MappingBundle\Tests\Unit\Transformers;

use Ehyiah\MappingBundle\Exceptions\TransformeException;
use Ehyiah\MappingBundle\Tests\Dummy\DummyMappedObject;
use Ehyiah\MappingBundle\Tests\Dummy\DummyTargetObject;
use Ehyiah\MappingBundle\Transformer\BooleanTransformer;
use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @coversDefaultClass \Ehyiah\MappingBundle\Transformer\BooleanTransformer
 */
final class BooleanTransformerTest extends KernelTestCase
{
    /**
     * @dataProvider provideTransformCases
     *
     * @covers ::transform
     * @covers ::reverseTransform
     */
    public function testTransform(string|int $data, bool $expected, array $options = [], $exception = false): void
    {
        $transformer = new BooleanTransformer();

        if ($exception) {
            $this->expectExceptionObject(new TransformeException('Can not transform to boolean value : ' . $data));
        }

        $result = $transformer->transform($data, $options, new DummyTargetObject(), new DummyMappedObject());

        $this->assertEquals($expected, $result);

        $result = $transformer->reverseTransform($data, $options, new DummyTargetObject(), new DummyMappedObject());

        $this->assertEquals($expected, $result);
    }

    public static function provideTransformCases(): Generator
    {
        yield ['true', true];
        yield ['false', false];
        yield ['2025-12-12', false, ['strict' => false]];
        yield ['2025-12-12', false, ['strict' => true], true];
        yield [1, true];
        yield [0, false];
    }

    /**
     * @dataProvider provideReserveTransformCases
     *
     * @covers ::reverseTransform
     * @covers ::transform
     */
    public function testReverseTransform(bool $data, mixed $expectedResult, array $options = []): void
    {
        $transformer = new BooleanTransformer();

        $result = $transformer->reverseTransform($data, $options, new DummyTargetObject(), new DummyMappedObject());

        $this->assertEquals($expectedResult, $result);

        $result = $transformer->transform($data, $options, new DummyTargetObject(), new DummyMappedObject());

        $this->assertEquals($expectedResult, $result);
    }

    public static function provideReserveTransformCases(): Generator
    {
        yield [true, 'true'];
        yield [true, '1', ['trueValue' => '1']];
        yield [true, 1, ['trueValue' => 1]];
        yield [true, 'on', ['trueValue' => 'on']];
        yield [false, 'false'];
        yield [false, 'off', ['falseValue' => 'off']];
        yield [false, 0, ['falseValue' => 0]];
    }
}
