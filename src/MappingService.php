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
    private function map(object $from, ?object $to, object $reference, bool $persist, bool $flush): object
    {
        $isFrom = $from === $reference;

        $modificationCount = 0;
        $mapping = $this->getPropertiesToMap($reference);

        if (null === $to) {
            $to = new $mapping['targetClass']();

            if (true === $persist) {
                $this->entityManager->persist($to);
            }
        }

        $propertyAccessor = new PropertyAccessor();

        foreach ($mapping['properties'] as $mappingName => $path) {
            $mappingTarget = $path['target'];

            $name = $isFrom ? $mappingName : $mappingTarget;
            $target = $isFrom ? $mappingTarget : $mappingName;

            if ($propertyAccessor->isWritable($to, $target)) {
                if ($propertyAccessor->isReadable($from, $name)) {
                    if (isset($path['transformer'])) {
                        $transformer = $this->transformationLocator->returnTransformer($path['transformer']);
                        $value = $transformer->transform($propertyAccessor->getValue($from, $name), $path['options'], $to, $from);
                    } elseif (isset($path['reverseTransformer'])) {
                        $reverseTransformer = $this->transformationLocator->returnReverseTransformer($path['reverseTransformer']);
                        $value = $reverseTransformer->reverseTransform($propertyAccessor->getValue($from, $name), $path['options'], $to, $from);
                    } else {
                        $value = $propertyAccessor->getValue($from, $name);
                    }

                    $propertyAccessor->setValue($to, $target, $value);
                    ++$modificationCount;

                    $this->mappingLogger->info('Mapping property into target object', [
                        'targetObject' => $to::class,
                        'target' => $target,
                        'value' => $value,
                        'withTransform' => (isset($path['transformer'], $transformer)) ? $transformer::class : false,
                        'withReverseTransformer' => (isset($path['reverseTransformer'], $reverseTransformer)) ? $reverseTransformer::class : false,
                    ]);
                }
            } else {
                $this->mappingLogger->alert('try to access not writable property in target object : ' . $to::class, [
                    'targetPath' => $path,
                    'sourceName' => $name,
                ]);
            }
        }

        if ($modificationCount > 0 && $flush) {
            $this->entityManager->flush();
        }

        return $to;
    }

    /**
     * @throws ReflectionException
     * @throws MappingException
     */
    public function mapToTarget(object $mappedObject, ?object $targetObject = null, bool $persist = false, bool $flush = false): object
    {
        return $this->map($mappedObject, $targetObject, $mappedObject, $persist, $flush);
    }

    /**
     * @throws ReflectionException
     * @throws MappingException
     */
    public function mapFromTarget(object $targetObject, object $mappedObject): object
    {
        return $this->map($targetObject, $mappedObject, $mappedObject, false, false);
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
