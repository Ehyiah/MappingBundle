<?php

namespace Ehyiah\MappingBundle\Tests\Integration;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Ehyiah\MappingBundle\Attributes\MappingAware;
use Ehyiah\MappingBundle\DependencyInjection\TransformerLocator;
use Ehyiah\MappingBundle\Exceptions\NotMappableObject;
use Ehyiah\MappingBundle\MappingService;
use Ehyiah\MappingBundle\Tests\Dummy\DummyMappedObject;
use Ehyiah\MappingBundle\Tests\Dummy\DummyMappedObjectWithoutAttribute;
use Ehyiah\MappingBundle\Tests\Dummy\DummyTargetObject;
use Ehyiah\MappingBundle\Transformer\DateTimeTransformer;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \Ehyiah\MappingBundle\MappingService
 */
final class MappingServiceTest extends TestCase
{
    private function createService(): MappingService
    {
        $transformerLocator = $this->createMock(TransformerLocator::class);

        $transformerLocator->method('returnTransformer')->willReturnCallback(fn(string $transformation) => new $transformation());
        $transformerLocator->method('returnReverseTransformer')->willReturnCallback(fn(string $transformation) => new $transformation());

        return new MappingService($this->createMock(EntityManagerInterface::class), $transformerLocator, $this->createMock(LoggerInterface::class));
    }

    /**
     * @covers \Ehyiah\MappingBundle\MappingService::mapToTarget
     */
    public function testMapToTarget(): void
    {
        $mappingService = $this->createService();

        $dto = new DummyMappedObject();
        $dto->string = 'just a string';
        $dto->boolean = true;
        $dto->withTransform = '2012-01-01';
        $dto->withReverseTransform = new DateTime('2012-01-01');
        $dto->withOtherDestination = 'the other destination';

        $result = $mappingService->mapToTarget($dto);

        $this->assertInstanceOf(DummyTargetObject::class, $result);
        $this->assertIsString($result->string);
        $this->assertEquals($dto->string, $result->string);
        $this->assertIsBool($result->boolean);
        $this->assertEquals($dto->boolean, $result->boolean);

        $this->assertInstanceOf(DateTime::class, $result->withTransform);
        $this->assertEquals(new DateTime('2012-01-01'), $result->withTransform);

        $this->assertIsString($result->withReverseTransform);
        $this->assertEquals((new DateTime('2012-01-01'))->format('Y/m/d'), $result->withReverseTransform);

        $this->assertEquals($dto->withOtherDestination, $result->theOtherDestination);

        $this->assertNull($result->notMappedProperty);
    }

    /**
     * @covers \Ehyiah\MappingBundle\MappingService::mapToTarget
     */
    public function testMapFromTarget(): void
    {
        $mappingService = $this->createService();

        $targetObject = new DummyTargetObject();
        $targetObject->string = 'just a string';
        $targetObject->boolean = true;
        $targetObject->notMappedProperty = 'i must not be mapped';
        $targetObject->withTransform = new DateTime('2012-01-01');
        $targetObject->withReverseTransform = (new DateTime('2012-01-01'))->format('Y/m/d');
        $targetObject->theOtherDestination = 'the other destination to be mapped';

        $result = $mappingService->mapFromTarget($targetObject, new DummyMappedObject());

        $this->assertInstanceOf(DummyMappedObject::class, $result);
        $this->assertIsString($result->string);
        $this->assertEquals($targetObject->string, $result->string);
        $this->assertIsBool($result->boolean);
        $this->assertEquals($targetObject->boolean, $result->boolean);

        $this->assertIsString($result->withTransform);
        $this->assertEquals($result->withTransform, (new DateTime('2012-01-01'))->format('Y/m/d'));

        $this->assertInstanceOf(DateTime::class, $result->withReverseTransform);
        $this->assertEquals(new DateTime('2012-01-01'), $result->withReverseTransform);

        $this->assertEquals($targetObject->theOtherDestination, $result->withOtherDestination);

        $this->assertEquals('i am not mapped', $result->notMappedProperty);
    }

    /**
     * @covers \Ehyiah\MappingBundle\MappingService::getPropertiesToMap
     */
    public function testGetPropertiesToMap(): void
    {
        $mappingService = $this->createService();

        $dto = new DummyMappedObject();

        $result = $mappingService->getPropertiesToMap($dto);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('targetClass', $result);
        $this->assertArrayHasKey('properties', $result);

        $this->assertEquals(DummyTargetObject::class, $result['targetClass']);

        $properties = $result['properties'];
        $this->assertArrayHasKey('string', $properties);
        $this->assertEquals('string', $properties['string']['target']);

        $this->assertArrayHasKey('boolean', $properties);
        $this->assertArrayHasKey('withTransform', $properties);
        $this->assertEquals(DateTimeTransformer::class, $properties['withTransform']['transformer']);
        $this->assertArrayHasKey('options', $properties['withTransform']);

        $this->assertEquals(DateTimeTransformer::class, $properties['withTransformAndOptions']['transformer']);
        $this->assertArrayHasKey('options', $properties['withTransformAndOptions']);
        $this->assertArrayHasKey('option1', $properties['withTransformAndOptions']['options']);
        $this->assertEquals('value1', $properties['withTransformAndOptions']['options']['option1']);

        $this->assertArrayHasKey('withReverseTransform', $properties);
        $this->assertEquals(DateTimeTransformer::class, $properties['withReverseTransform']['reverseTransformer']);

        $this->assertEquals(DateTimeTransformer::class, $properties['withReverseTransformAndOptions']['reverseTransformer']);
        $this->assertArrayHasKey('options', $properties['withReverseTransformAndOptions']);
        $this->assertArrayHasKey('option1', $properties['withReverseTransformAndOptions']['options']);
        $this->assertEquals('value1', $properties['withReverseTransformAndOptions']['options']['option1']);

        $this->assertArrayHasKey('withOtherDestination', $properties);
        $this->assertEquals('theOtherDestination', $properties['withOtherDestination']['target']);
    }

    /**
     * @covers \Ehyiah\MappingBundle\MappingService::getPropertiesToMap
     */
    public function testMissingAttributeOnClass(): void
    {
        $mappingService = $this->createService();

        $this->expectExceptionObject(new NotMappableObject('Can not automap object, because object is not using Attribute : ' . MappingAware::class));

        $dto = new DummyMappedObjectWithoutAttribute();
        $mappingService->getPropertiesToMap($dto);
    }
}
