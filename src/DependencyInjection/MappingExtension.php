<?php

namespace Ehyiah\MappingBundle\DependencyInjection;

use Ehyiah\MappingBundle\Transformer\Interfaces\TransformerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class MappingExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config/packages'));
        $loader->load('monolog.yaml');
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(TransformerInterface::class)
            ->addTag('ehyiah.mapping_bundle.transformer')
        ;

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.xml');
    }
}
