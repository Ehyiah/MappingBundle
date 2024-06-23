<?php

namespace Ehyiah\MappingBundle\Transformer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Ehyiah\MappingBundle\Exceptions\TransformeException;
use Ehyiah\MappingBundle\Exceptions\WrongDataTypeTransformerException;
use Ehyiah\MappingBundle\MappingServiceInterface;
use Ehyiah\MappingBundle\Transformer\Interfaces\TransformerInterface;
use Exception;
use Symfony\Component\PropertyAccess\PropertyAccessor;

final class CollectionTransformer implements TransformerInterface
{
    public function __construct(
        private readonly MappingServiceInterface $mappingService,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function transformationSupports(): string
    {
        return self::class;
    }

    /**
     * @param array<mixed> $options
     *
     * @return ?Collection<object>
     *
     * @throws Exception
     */
    public function transform(mixed $data, array $options, object $targetObject, object $mappedObject): ?Collection
    {
        if (null === $data) {
            return null;
        }

        if (!$data instanceof Collection) {
            throw new WrongDataTypeTransformerException('Data is supposed to be a collection to use transform : ' . self::class . ' ' . gettype($data) . ' provided');
        }

        if (!isset($options['targetClass'])) {
            throw new TransformeException('Missing mandatory option targetClass');
        }

        /** @var class-string $fcqn */
        $fcqn = $options['targetClass'];

        $propertyAccessor = new PropertyAccessor();

        $updatedIds = [];
        $targetCollection = new ArrayCollection();
        foreach ($data as $datum) {
            $target = null;
            if ($propertyAccessor->isReadable($datum, 'id')) {
                $id = $propertyAccessor->getValue($datum, 'id');
                $target = null !== $id ? $this->em->getRepository($fcqn)->find($id) : $target;
            }

            $target = $this->mappingService->mapToTarget($datum, $target);
            $targetCollection->add($target);

            if ($propertyAccessor->isReadable($target, 'id')) {
                $targetId = $propertyAccessor->getValue($target, 'id');
                if (!in_array($targetId, $updatedIds, true)) {
                    $updatedIds[] = $targetId;
                }
            }
        }

        if (isset($options['fillFrom'])) {
            $initialCollection = $propertyAccessor->getValue($targetObject, $options['fillFrom']);

            foreach ($initialCollection as $initialElement) {
                $targetCollection->add($initialElement);
            }
        }

        return $targetCollection;
    }

    /**
     * @param array<mixed> $options
     *
     * @return ?Collection<object>
     *
     * @throws Exception
     */
    public function reverseTransform(mixed $data, array $options, object $targetObject, object $mappedObject): ?Collection
    {
        if (null === $data) {
            return null;
        }

        if (!$data instanceof Collection) {
            throw new WrongDataTypeTransformerException('Data is supposed to be a collection to use transform : ' . self::class . ' ' . gettype($data) . ' provided');
        }

        if (!isset($options['sourceClass'])) {
            throw new TransformeException('Missing mandatory option sourceClass');
        }

        $sourceCollection = new ArrayCollection();
        foreach ($data as $datum) {
            $source = $this->mappingService->mapFromTarget($datum, new $options['sourceClass']());
            $sourceCollection->add($source);
        }

        return $sourceCollection;
    }
}
