<?php
declare(strict_types=1);

namespace Gdbots\Bundle\AppBundle;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * Using this class assumes the use of @see \Gdbots\Bundle\AppBundle\Composer\ScriptHandler::installConstantsFile
 * and having it run in the "post-install-cmd" and "post-update-cmd" composer event hooks.
 *
 * The constants written to your project's document root (by default into .constants.php) are then
 * included, ideally via composer "files" before any of your code runs.
 */
abstract class AbstractAppKernel extends Kernel implements AppKernel
{
    use MicroKernelTrait;

    protected const CONFIG_EXTS = '.{php,xml,yaml,yml}';

    /** @var string */
    protected $appBuild;

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $contents = require $this->getConfigDir() . '/bundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getConfigDir();

        $loader->load($confDir . '/packages/*' . static::CONFIG_EXTS, 'glob');

        if (is_dir($confDir . '/packages/' . $this->environment)) {
            $loader->load($confDir . '/packages/' . $this->environment . '/**/*' . static::CONFIG_EXTS, 'glob');
        }

        $loader->load($confDir . '/services' . static::CONFIG_EXTS, 'glob');
        $loader->load($confDir . '/services_' . $this->environment . static::CONFIG_EXTS, 'glob');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $confDir = $this->getConfigDir();

        if (is_dir($confDir . '/routes/')) {
            $routes->import($confDir . '/routes/*' . static::CONFIG_EXTS, '/', 'glob');
        }

        if (is_dir($confDir . '/routes/' . $this->environment)) {
            $routes->import($confDir . '/routes/' . $this->environment . '/**/*' . static::CONFIG_EXTS, '/', 'glob');
        }

        $routes->import($confDir . '/routes' . static::CONFIG_EXTS, '/', 'glob');
    }

    /**
     * {@inheritdoc}
     */
    public function getAppVendor(): string
    {
        return APP_VENDOR;
    }

    /**
     * {@inheritdoc}
     */
    public function getAppName(): string
    {
        return APP_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getAppVersion(): string
    {
        return APP_VERSION;
    }

    /**
     * {@inheritdoc}
     */
    public function getAppBuild(): string
    {
        if (null === $this->appBuild) {
            if ($this->isDebug()) {
                $this->appBuild = (string)explode('.', (string)$this->getStartTime())[0];
            } else {
                $this->appBuild = APP_BUILD;
            }
        }

        return $this->appBuild;
    }

    /**
     * {@inheritdoc}
     */
    public function getAppDeploymentId(): string
    {
        if ($this->isDebug()) {
            return $this->getAppBuild();
        }

        return APP_DEPLOYMENT_ID;
    }

    /**
     * {@inheritdoc}
     */
    public function getAppDevBranch(): string
    {
        return APP_DEV_BRANCH;
    }

    /**
     * {@inheritdoc}
     */
    public function getSystemMacAddress(): string
    {
        return SYSTEM_MAC_ADDRESS;
    }

    /**
     * {@inheritdoc}
     */
    public function getCloudProvider(): string
    {
        return CLOUD_PROVIDER;
    }

    /**
     * {@inheritdoc}
     */
    public function getCloudRegion(): string
    {
        return CLOUD_REGION;
    }

    /**
     * {@inheritdoc}
     */
    public function getCloudZone(): string
    {
        return CLOUD_ZONE;
    }

    /**
     * {@inheritdoc}
     */
    public function getCloudInstanceId(): string
    {
        return CLOUD_INSTANCE_ID ?: $this->getSystemMacAddress();
    }

    /**
     * {@inheritdoc}
     */
    public function getCloudInstanceType(): string
    {
        return CLOUD_INSTANCE_TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectDir()
    {
        return APP_ROOT_DIR;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigDir()
    {
        return $_SERVER['APP_CONFIG_DIR'] ?? $this->getProjectDir() . '/config';
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return $_SERVER['APP_CACHE_DIR'] ?? $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return $_SERVER['APP_LOGS_DIR'] ?? $this->getProjectDir() . '/var/logs';
    }

    /**
     * @return string
     */
    public function getTmpDir()
    {
        return $_SERVER['APP_TMP_DIR'] ?? $this->getProjectDir() . '/var/tmp';
    }

    /**
     * Calls parent to get builtin kernel parameters and then adds a few key settings.
     *
     * @return array
     */
    protected function getKernelParameters()
    {
        $parameters = parent::getKernelParameters();
        $parameters['app_vendor'] = $this->getAppVendor();
        $parameters['app_name'] = $this->getAppName();
        $parameters['app_version'] = $this->getAppVersion();
        $parameters['app_build'] = $this->getAppBuild();
        $parameters['app_deployment_id'] = $this->getAppDeploymentId();
        $parameters['app_dev_branch'] = $this->getAppDevBranch();
        $parameters['system_mac_address'] = $this->getSystemMacAddress();
        $parameters['cloud_provider'] = $this->getCloudProvider();
        $parameters['cloud_region'] = $this->getCloudRegion();
        $parameters['cloud_zone'] = $this->getCloudZone();
        $parameters['cloud_instance_id'] = $this->getCloudInstanceId();
        $parameters['cloud_instance_type'] = $this->getCloudInstanceType();

        $parameters['kernel.config_dir'] = $this->getConfigDir();
        if (!isset($parameters['kernel.tmp_dir'])) {
            $parameters['kernel.tmp_dir'] = realpath($this->getTmpDir()) ?: $this->getTmpDir();
        }

        // convenient flags for environments
        $env = strtolower(trim($this->environment));
        $parameters['is_production'] = 'prod' === $env || 'production' === $env ? true : false;
        $parameters['is_not_production'] = !$parameters['is_production'];

        return $parameters;
    }
}
