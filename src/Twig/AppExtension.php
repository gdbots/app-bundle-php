<?php
declare(strict_types=1);

namespace Gdbots\Bundle\AppBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

final class AppExtension extends AbstractExtension implements GlobalsInterface
{
    /**
     * Constants from the container to return as globals for twig templating.
     *
     * @const string[]
     */
    private const APP_GLOBALS = [
        'app_env',
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
    ];

    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('gdpr_applies', [GDPR::class, 'applies']),
        ];
    }

    public function getGlobals()
    {
        $resolver = $this->container->get('gdbots_app.device_view_resolver');
        $globals = [
            'device_view'    => $resolver->resolve($_SERVER['DEVICE_VIEW'] ?? null),
            'viewer_country' => strtoupper(trim((string)($_SERVER['VIEWER_COUNTRY'] ?? ''))),
        ];

        foreach (self::APP_GLOBALS as $v) {
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
