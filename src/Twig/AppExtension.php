<?php

namespace Gdbots\Bundle\AppBundle\Twig;

use Gdbots\Bundle\AppBundle\DeviceViewResolver;
use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * Constants from the container to return as globals for twig templating.
     * @var array
     */
    protected $appConstants = [
        'app_vendor',
        'app_name',
        'app_version',
        'app_build',
        'app_dev_branch',
        'system_mac_address',
        'cloud_provider',
        'cloud_region',
        'cloud_zone',
        'cloud_instance_id',
        'cloud_instance_type',
        'is_production',
        'is_not_production',
    ];

    /** @var ContainerInterface */
    protected $container;

    /** @var DeviceViewResolver */
    protected $deviceViewResolver;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->deviceViewResolver = $container->get('gdbots_app.device_view_resolver');

        /** @var FilesystemLoader $loader */
        $loader = $container->get('twig.loader');
        $loader->prependPath(
            $container->getParameter('kernel.root_dir').'/Resources/views/'.
            $this->deviceViewResolver->resolve(getenv('DEVICE_VIEW'))
        );
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        $globals = [
            'device_view' => $this->deviceViewResolver->resolve(getenv('DEVICE_VIEW')),
            'is_'.$this->container->getParameter('kernel.environment').'_environment' => true,
        ];

        foreach ($this->appConstants as $v) {
            $globals[$v] = $this->container->getParameter($v);
        }

        return $globals;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'gdbots_app_extension';
    }
}
