<?php

namespace Ehyiah\MappingBundle;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Ehyiah\MappingBundle\Attributes\MappingAware;
use Ehyiah\MappingBundle\Exceptions\MappingException;
use Ehyiah\MappingBundle\Exceptions\NotMappableObject;
use Ehyiah\MappingBundle\Transformer\TransformerLocator;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class MappingService
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
    public function mapToTarget(object $mappedObject, ?object $targetObject = null, bool $persist = false, bool $flush = false): object
    {
        $mapping = $this->getPropertiesToMap($mappedObject);

        if (null === $targetObject) {
            $targetObject = new $mapping['targetClass']();

            if (true === $persist) {
                $this->entityManager->persist($targetObject);
            }
        }

        $propertyAccessor = new PropertyAccessor();
        $modificationCount = 0;

        foreach ($mapping['properties'] as $name => $path) {
            $target = $path['target'];

            if ($propertyAccessor->isWritable($targetObject, $target)) {
                if ($propertyAccessor->isReadable($mappedObject, $name)) {
                    if (isset($path['transformer'])) {
                        $transformer = $this->transformationLocator->returnTransformer($path['transformer']);
                        $value = $transformer->transform($propertyAccessor->getValue($mappedObject, $name), $path['options'], $targetObject, $mappedObject);
                    } else {
                        $value = $propertyAccessor->getValue($mappedObject, $name);
                    }

                    if ($value instanceof Collection && isset($path['clearCollection']) && false === $path['clearCollection']) {
                        $oldValues = $propertyAccessor->getValue($targetObject, $target);
                        $keep = [];
                        foreach ($oldValues as $oldValue) {
                            $keep[$oldValue->getId()] = $oldValue;
                        }

                        foreach ($value as $v) {
                            $vMapping = $this->getPropertiesToMap($v);
                            $newElement = new $vMapping['targetClass']();
                            $oldId = $propertyAccessor->getValue($v, 'id');
                            if (null !== $oldId && array_key_exists($oldId, $keep)) {
                                $newElement = $this->entityManager->getRepository($newElement::class)->findOneBy(['id' => $oldId]);
                                $newElement = $this->mapToTarget($v, $newElement);
                                $keep[$oldId] = $newElement;
                            } elseif (null === $oldId) {
                                $keep[] = $this->mapToTarget($v, $newElement);
                            } else {
                                $this->mappingLogger->alert('try to edit not existing element : ' . $targetObject::class, [
                                    'target' => $path,
                                    'dtoPropertyName' => $name,
                                    'id' => $oldId,
                                ]);
                            }
                        }

                        $propertyAccessor->setValue($targetObject, $target, $keep);
                    } else {
                        if (null !== $value) {
                            $propertyAccessor->setValue($targetObject, $target, $value);
                        }
                    }

                    ++$modificationCount;

                    $this->mappingLogger->info('Mapping property into target object', [
                        'targetObject' => $targetObject::class,
                        'target' => $target,
                        'value' => $value,
                        'withTransform' => (isset($path['transformer'], $transformer)) ? $transformer::class : false,
                    ]);
                }
            } else {
                $this->mappingLogger->alert('try to access not writable property in target object : ' . $targetObject::class, [
                    'targetPath' => $path,
                    'sourceName' => $name,
                ]);
            }
        }

        if ($modificationCount > 0 && $flush) {
            $this->entityManager->flush();
        }

        return $targetObject;
    }

    /**
     * @throws ReflectionException
     * @throws MappingException
     */
    public function mapFromTarget(object $targetObject, object $mappedObject): object
    {
        $mapping = $this->getPropertiesToMap($mappedObject);

        $propertyAccessor = new PropertyAccessor();

        foreach ($mapping['properties'] as $name => $path) {
            $origin = $path['target'];
            $target = $name;

            if ($propertyAccessor->isWritable($mappedObject, $target)) {
                if ($propertyAccessor->isReadable($targetObject, $origin)) {
                    if (isset($path['reverseTransformer'])) {
                        $reverseTransformer = $this->transformationLocator->returnReverseTransformer($path['reverseTransformer']);
                        $value = $reverseTransformer->reverseTransform($propertyAccessor->getValue($targetObject, $origin), $path['options'], $targetObject, $mappedObject);
                    } else {
                        $value = $propertyAccessor->getValue($targetObject, $origin);
                    }

                    $propertyAccessor->setValue($mappedObject, $target, $value);

                    $this->mappingLogger->info('Mapping property into target Object', [
                        'targetObject' => $mappedObject::class,
                        'target' => $target,
                        'value' => $value,
                        'withReverseTransformer' => (isset($path['reverseTransformer'], $reverseTransformer)) ? $reverseTransformer::class : false,
                    ]);
                }
            } else {
                $this->mappingLogger->alert('try to access not writable property in target Object : ' . $mappedObject::class, [
                    'target' => $path,
                    'sourcePropertyName' => $name,
                ]);
            }
        }

        return $mappedObject;
    }

    /**
     * @return array<mixed>
     *
     * @throws ReflectionException
     * @throws MappingException
     */
    public function getPropertiesToMap(object $mappedObject): array
    {
        $reflection = new ReflectionClass($mappedObject::class);
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
                    $targetPath = $attributeToMap->newInstance()->target ?? $property->getName();
                    $mapping['targetClass'] = $targetClass;
                    $mapping['properties'][$property->getName()]['target'] = $targetPath;

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
