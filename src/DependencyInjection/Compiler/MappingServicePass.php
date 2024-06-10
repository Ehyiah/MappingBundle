<?php

namespace Ehyiah\MappingBundle\DependencyInjection\Compiler;

use Ehyiah\MappingBundle\MappingService;
use Ehyiah\MappingBundle\MappingServiceInterface;
use LogicException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class MappingServicePass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $taggedServices = $container->findTaggedServiceIds('ehyiah.mapping_bundle.mapping_service');

        if (count($taggedServices) > 1 && array_key_exists(MappingService::class, $taggedServices)) {
            unset($taggedServices[MappingService::class]);
        }

        if (1 === count($taggedServices)) {
            $definition = new Definition(MappingServiceInterface::class);
            $container->setDefinition(MappingServiceInterface::class, $definition);
            $container->setAlias(MappingServiceInterface::class, array_key_first($taggedServices));
        } else {
            throw new LogicException('Please create a single MappingService service. You got ' . count($taggedServices) . ' services.');
        }
    }
}
