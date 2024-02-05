<?php

namespace Ehyiah\MappingBundle\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Ehyiah\MappingBundle\Tests\Dummy\DummyMappedObject;
use Ehyiah\MappingBundle\Tests\Dummy\DummyTargetObject;
use Ehyiah\MappingBundle\MappingService;
use Ehyiah\MappingBundle\Transformer\DateTimeTransformer;
use Ehyiah\MappingBundle\Transformer\TransformerLocator;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @coversDefaultClass \Ehyiah\MappingBundle\MappingService
 */
class MappingServiceTest extends KernelTestCase
{
    private $entityManager;
    private $transformerLocator;
    private $logger;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->transformerLocator = $this->createMock(TransformerLocator::class);
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    /**
     * @covers \Ehyiah\MappingBundle\MappingService::mapToTarget
     */
    public function testMapToTarget(): void
    {
        $mappingService = new MappingService($this->entityManager, $this->transformerLocator, $this->logger);

        $dto = new DummyMappedObject();
        $dto->string = 'just a string';
        $dto->boolean = true;
        $dto->withOtherDestination = 'the other destination';

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
     * @covers \Ehyiah\MappingBundle\MappingService::getPropertiesToMap
     */
    public function testGetPropertiesToMap(): void
    {
        $mappingService = new MappingService($this->entityManager, $this->transformerLocator, $this->logger);

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

        $this->assertArrayHasKey('withReverseTransform', $properties);
        $this->assertEquals(DateTimeTransformer::class, $properties['withReverseTransform']['reverseTransformer']);

        $this->assertEquals(DateTimeTransformer::class, $properties['withReverseTransformAndOptions']['reverseTransformer']);
        $this->assertArrayHasKey('options', $properties['withReverseTransformAndOptions']);

        $this->assertArrayHasKey('withOtherDestination', $properties);
        $this->assertEquals('theOtherDestination', $properties['withOtherDestination']['target']);
    }
}
