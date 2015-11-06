<?php

namespace Gdbots\Bundle\AppBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class GdbotsAppExtension extends Extension
{
    /**
     * @param array $config
     * @param ContainerBuilder $container
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $this->addClassesToCompile([
            'Gdbots\\Bundle\\AppBundle\\AbstractAppKernel',
            'Gdbots\\Bundle\\AppBundle\\AppKernel',
            'Gdbots\\Bundle\\AppBundle\\GdbotsAppBundle',
        ]);
    }
}
