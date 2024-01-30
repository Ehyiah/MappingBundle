<?php

namespace Ehyiah\MappingBundle\DependencyInjection\Compiler;

use Ehyiah\MappingBundle\Transformer\TransformerLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class TransformerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->findDefinition(TransformerLocator::class);

        foreach ($container->findTaggedServiceIds('app.transformer.handler') as $id => $tag) {
            $definition->addMethodCall('addTransformer', [new Reference($id)]);
        }
    }
}
