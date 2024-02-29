<?php

namespace Ehyiah\MappingBundle;

use Ehyiah\MappingBundle\DependencyInjection\Compiler\TransformerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

// More details on https://symfony.com/doc/current/bundles/configuration.html#using-the-abstractbundle-class
class MappingBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TransformerPass());
    }
}
