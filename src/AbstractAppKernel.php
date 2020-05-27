<?php
declare(strict_types=1);

namespace Gdbots\Bundle\AppBundle;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

abstract class AbstractAppKernel extends Kernel implements AppKernel
{
    use MicroKernelTrait;

    protected const CONFIG_EXTS = '.{php,xml,yaml,yml}';
    protected ?string $appBuild = null;

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
    public function getAppEnv(): string
    {
        return $_SERVER['APP_ENV'] ?? 'dev';
    }

    /**
     * {@inheritdoc}
     */
    public function getAppVendor(): string
    {
        return $_SERVER['APP_VENDOR'] ?? 'unknown';
    }

    /**
     * {@inheritdoc}
     */
    public function getAppName(): string
    {
        return $_SERVER['APP_NAME'] ?? 'unknown';
    }

    /**
     * {@inheritdoc}
     */
    public function getAppVersion(): string
    {
        return $_SERVER['APP_VERSION'] ?? 'N.N.N';
    }

    /**
     * {@inheritdoc}
     */
    public function getAppBuild(): string
    {
        if (null === $this->appBuild) {
            $build = (string)explode('.', (string)$this->getStartTime())[0];
            if ($this->isDebug()) {
                $this->appBuild = $build;
            } else {
                $this->appBuild = $_SERVER['APP_BUILD'] ?? $build;
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

        return $_SERVER['APP_DEPLOYMENT_ID'] ?? $this->getAppBuild();
    }

    /**
     * {@inheritdoc}
     */
    public function getAppDevBranch(): string
    {
        return $_SERVER['APP_DEV_BRANCH'] ?? 'master';
    }

    /**
     * {@inheritdoc}
     */
    public function getSystemMacAddress(): string
    {
        return $_SERVER['SYSTEM_MAC_ADDRESS'] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function getCloudProvider(): string
    {
        return $_SERVER['CLOUD_PROVIDER'] ?? 'private';
    }

    /**
     * {@inheritdoc}
     */
    public function getCloudRegion(): string
    {
        return $_SERVER['CLOUD_REGION'] ?? 'unknown';
    }

    /**
     * {@inheritdoc}
     */
    public function getCloudZone(): string
    {
        return $_SERVER['CLOUD_ZONE'] ?? 'unknown';
    }

    /**
     * {@inheritdoc}
     */
    public function getCloudInstanceId(): string
    {
        return $_SERVER['CLOUD_INSTANCE_ID'] ?? 'unknown';
    }

    /**
     * {@inheritdoc}
     */
    public function getCloudInstanceType(): string
    {
        return $_SERVER['CLOUD_INSTANCE_TYPE'] ?? 'unknown';
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
        $parameters['kernel.config_dir'] = $this->getConfigDir();
        if (!isset($parameters['kernel.tmp_dir'])) {
            $parameters['kernel.tmp_dir'] = realpath($this->getTmpDir()) ?: $this->getTmpDir();
        }

        return $parameters;
    }
}
