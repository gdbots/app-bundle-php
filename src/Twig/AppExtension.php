<?php
declare(strict_types=1);

namespace Gdbots\Bundle\AppBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

class AppExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * Constants from the container to return as globals for twig templating.
     *
     * @var string[]
     */
    protected $appConstants = [
        'app_vendor',
        'app_name',
        'app_version',
        'app_build',
        'app_deployment_id',
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

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        $resolver = $this->container->get('gdbots_app.device_view_resolver');
        $globals = [
            'device_view' => $resolver->resolve(getenv('DEVICE_VIEW') ?: null),
            'is_' . $this->container->getParameter('kernel.environment') . '_environment' => true,
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
