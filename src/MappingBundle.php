<?php

namespace Ehyiah\MappingBundle;

use Ehyiah\MappingBundle\DependencyInjection\Compiler\MappingServicePass;
use Ehyiah\MappingBundle\DependencyInjection\Compiler\TransformerPass;
use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

// More details on https://symfony.com/doc/current/bundles/configuration.html#using-the-abstractbundle-class
class MappingBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TransformerPass());
        $container->addCompilerPass(new MappingServicePass());
    }

    /**
     * @throws Exception
     */
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        parent::prependExtension($container, $builder);

        $loader = new YamlFileLoader($builder, new FileLocator(__DIR__ . '/../config/packages'));
        $loader->load('monolog.yaml');
    }

    /**
     * @param array<string,array<string,mixed>> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        parent::loadExtension($config, $container, $builder);

        $container->import('../config/services.yaml');
    }
}
