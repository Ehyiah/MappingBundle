<?php

namespace Ehyiah\MappingBundle\DependencyInjection\Compiler;

use Ehyiah\MappingBundle\Transformer\TransformerLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class ReverseTransformPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->findDefinition(TransformerLocator::class);

        foreach ($container->findTaggedServiceIds('ehyiah.mapping_bundle.reverse_transformer') as $id => $tag) {
            $definition->addMethodCall('addReverseTransformer', [new Reference($id)]);
        }
    }
}
