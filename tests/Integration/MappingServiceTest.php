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
use Ehyiah\MappingBundle\Transformer\StringToDateTimeTransformer;
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

        $mappedObject = new DummyMappedObject();
        $mappedObject->string = 'just a string';
        $mappedObject->boolean = true;
        $mappedObject->date = '2012-01-01';
        $mappedObject->withOtherDestination = 'the other destination';

        $result = $mappingService->mapToTarget($mappedObject);

        $this->assertInstanceOf(DummyTargetObject::class, $result);

        $this->assertIsString($result->string);
        $this->assertEquals($mappedObject->string, $result->string);

        $this->assertIsBool($result->boolean);
        $this->assertEquals($mappedObject->boolean, $result->boolean);

        $this->assertInstanceOf(DateTime::class, $result->date);
        $this->assertEquals(new DateTime('2012-01-01'), $result->date);

        $this->assertEquals($mappedObject->withOtherDestination, $result->theOtherDestination);

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
        $targetObject->date = new DateTime('2012-01-01');
        $targetObject->theOtherDestination = 'the other destination to be mapped';

        $result = $mappingService->mapFromTarget($targetObject, new DummyMappedObject());

        $this->assertInstanceOf(DummyMappedObject::class, $result);

        $this->assertIsString($result->string);
        $this->assertEquals($targetObject->string, $result->string);

        $this->assertIsBool($result->boolean);
        $this->assertEquals($targetObject->boolean, $result->boolean);

        $this->assertIsString($result->date);
        $this->assertEquals('2012/01/01', $result->date);

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

        $this->assertArrayHasKey('date', $properties);
        $this->assertEquals(StringToDateTimeTransformer::class, $properties['date']['transformer']);
        $this->assertArrayHasKey('options', $properties['date']);

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
