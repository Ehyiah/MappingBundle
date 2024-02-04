<?php

namespace Ehyiah\MappingBundle;

use Doctrine\ORM\EntityManagerInterface;
use Ehyiah\MappingBundle\Attributes\MappingAware;
use Ehyiah\MappingBundle\Exceptions\MappingException;
use Ehyiah\MappingBundle\Exceptions\NotMappableObject;
use Ehyiah\MappingBundle\Transformer\TransformerLocator;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class MappingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TransformerLocator $transformationLocator,
        private LoggerInterface $mappingLogger,
    ) {
    }

    /**
     * @throws ReflectionException
     * @throws MappingException
     */
    public function mapToTargetEntity(object $dto, object $entity = null, bool $flush = true): object
    {
        $mapping = $this->getPropertiesToMap($dto);

        if (null === $entity) {
            $entity = new $mapping['targetClass']();
            $this->entityManager->persist($entity);
        }

        $propertyAccessor = new PropertyAccessor();
        $modificationCount = 0;

        foreach ($mapping['properties'] as $name => $path) {
            $target = $path['target'];

            if ($propertyAccessor->isWritable($entity, $target)) {
                if ($propertyAccessor->isReadable($dto, $name)) {
                    if (isset($path['transformer'])) {
                        $transformer = $this->transformationLocator->returnTransformer($path['transformer']);
                        $value = $transformer->transform($propertyAccessor->getValue($dto, $name), $path['options'], $entity, $dto);
                    } else {
                        $value = $propertyAccessor->getValue($dto, $name);
                    }

                    $propertyAccessor->setValue($entity, $target, $value);
                    ++$modificationCount;

                    $this->mappingLogger->info('Mapping property into entity', [
                        'entity' => $entity->getId(),
                        'target' => $target,
                        'value' => $value,
                        'withTransform' => (isset($path['transformer'], $transformer)) ? $transformer::class : false,
                    ]);
                }
            } else {
                $this->mappingLogger->alert('try to access not writable property in Entity : ' . $entity::class, [
                    'target' => $path,
                    'dtoPropertyName' => $name,
                ]);
            }
        }

        if ($modificationCount > 0 && $flush) {
            $this->entityManager->flush();
        }

        return $entity;
    }

    /**
     * @throws ReflectionException
     * @throws MappingException
     */
    public function mapFromTarget(object $entity, object $dto): object
    {
        $mapping = $this->getPropertiesToMap($dto);

        $propertyAccessor = new PropertyAccessor();

        foreach ($mapping['properties'] as $name => $path) {
            $origin = $path['target'];
            $target = $name;

            if ($propertyAccessor->isWritable($dto, $target)) {
                if ($propertyAccessor->isReadable($entity, $origin)) {
                    if (isset($path['reverseTransformer'])) {
                        $reverseTransformer = $this->transformationLocator->returnReverseTransformer($path['reverseTransformer']);
                        $value = $reverseTransformer->reverseTransform($propertyAccessor->getValue($entity, $origin), $path['options'], $entity, $dto);
                    } else {
                        $value = $propertyAccessor->getValue($entity, $origin);
                    }

                    $propertyAccessor->setValue($dto, $target, $value);

                    $this->mappingLogger->info('Mapping property into DTO', [
                        'dto' => $dto::class,
                        'target' => $target,
                        'value' => $value,
                        'withReverseTransformer' => (isset($path['reverseTransformer'], $reverseTransformer)) ? $reverseTransformer::class : false,
                    ]);
                }
            } else {
                $this->mappingLogger->alert('try to access not writable property in DTO : ' . $dto::class, [
                    'target' => $path,
                    'dtoPropertyName' => $name,
                ]);
            }
        }

        return $dto;
    }

    /**
     * @return array<mixed>
     *
     * @throws ReflectionException
     * @throws MappingException
     */
    public function getPropertiesToMap(object $dto): array
    {
        $reflection = new ReflectionClass($dto::class);
        $attributesClass = $reflection->getAttributes(MappingAware::class);

        if (0 === count($attributesClass)) {
            throw new NotMappableObject('Can not automap object, because object is not using Attribute : ' . MappingAware::class);
        }

        $mapping = [];
        $properties = $reflection->getProperties();

        foreach ($attributesClass as $attributeClass) {
            $targetClass = $attributeClass->newInstance()->target;
            $mapping['targetClass'] = [];

            foreach ($properties as $property) {
                $attributesToMap = $property->getAttributes(MappingAware::class);
                foreach ($attributesToMap as $attributeToMap) {
                    $entityPath = $attributeToMap->newInstance()->target ?? $property->getName();
                    $mapping['targetClass'] = $targetClass;
                    $mapping['properties'][$property->getName()]['target'] = $entityPath;

                    if (null !== $attributeToMap->newInstance()->transformer) {
                        $mapping['properties'][$property->getName()]['transformer'] = $attributeToMap->newInstance()->transformer;
                        $mapping['properties'][$property->getName()]['options'] = $attributeToMap->newInstance()->options;
                    }
                    if (null !== $attributeToMap->newInstance()->reverseTransformer) {
                        $mapping['properties'][$property->getName()]['reverseTransformer'] = $attributeToMap->newInstance()->reverseTransformer;
                        $mapping['properties'][$property->getName()]['options'] = $attributeToMap->newInstance()->options;
                    }
                }
            }
        }

        if (null === $mapping['targetClass']) {
            throw new NotMappableObject('Can not automap object, because target class is not specified on class Attribute : ' . MappingAware::class);
        }

        $this->mappingLogger->info('Properties to map', [$mapping]);

        return $mapping;
    }
}
