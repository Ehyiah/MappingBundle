<?php

namespace Ehyiah\MappingBundle\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Ehyiah\MappingBundle\Attributes\MappingAware;
use Ehyiah\MappingBundle\Exceptions\NotMappableObject;
use Ehyiah\MappingBundle\Tests\Dummy\DummyMappedObject;
use Ehyiah\MappingBundle\Tests\Dummy\DummyMappedObjectWithoutAttribute;
use Ehyiah\MappingBundle\Tests\Dummy\DummyTargetObject;
use Ehyiah\MappingBundle\MappingService;
use Ehyiah\MappingBundle\Transformer\DateTimeTransformer;
use Ehyiah\MappingBundle\Transformer\TransformerLocator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \Ehyiah\MappingBundle\MappingService
 */
final class MappingServiceTest extends TestCase
{
    private $entityManager;
    private $logger;

    private function createService(): MappingService
    {
        $transformerLocator = $this->createMock(TransformerLocator::class);

        $transformerLocator->method('returnTransformer')->willReturnCallback(fn(string $transformation) => new $transformation());
        $transformerLocator->method('returnReverseTransformer')->willReturnCallback(fn(string $transformation) => new $transformation());

        return new MappingService($this->entityManager, $transformerLocator, $this->logger);
    }

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
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
        $dto->withOtherDestination = 'the other destination';
        $dto->withTransform = '2012-01-01';
        $dto->withReverseTransform = new \DateTime('now');

        $result = $mappingService->mapToTarget($dto);

        $this->assertInstanceOf(DummyTargetObject::class, $result);
        $this->assertEquals($dto->string, $result->string);
        $this->assertIsString($result->string);
        $this->assertEquals($dto->boolean, $result->boolean);
        $this->assertIsBool($result->boolean);
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
        $targetObject->theOtherDestination = 'the other destination to be mapped';
        $targetObject->boolean = true;
        $targetObject->notMappedProperty = 'i must not be mapped';

        $result = $mappingService->mapFromTarget($targetObject, new DummyMappedObject());

        $this->assertInstanceOf(DummyMappedObject::class, $result);
        $this->assertEquals($targetObject->string, $result->string);
        $this->assertIsString($result->string);
        $this->assertEquals($targetObject->boolean, $result->boolean);
        $this->assertIsBool($result->boolean);
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
