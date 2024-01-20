<?php
declare(strict_types=1);

namespace Gdbots\Bundle\AppBundle;

use Psr\Container\ContainerInterface as PsrContainerInterface;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

abstract class AbstractAppKernel extends Kernel implements AppKernel
{
    use MicroKernelTrait;

    protected const CONFIG_EXTS = '.{php,xml,yaml,yml}';
    protected ?string $appBuild = null;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $parameters = $container->parameters();
        $parameters->set('.container.dumper.inline_class_loader', true);
        $parameters->set('.container.dumper.inline_factories', true);
        $confDir = $this->getConfigDir();

        $container->import($confDir . '/packages/*' . static::CONFIG_EXTS);

        if (is_dir($confDir . '/packages/' . $this->environment)) {
            $container->import($confDir . '/packages/' . $this->environment . '/**/*' . static::CONFIG_EXTS);
        }

        $container->import($confDir . '/services' . static::CONFIG_EXTS);
        $container->import($confDir . '/services_' . $this->environment . static::CONFIG_EXTS);

        $container->services()->alias(ContainerInterface::class, 'service_container');
        $container->services()->alias(PsrContainerInterface::class, 'service_container');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $confDir = $this->getConfigDir();

        if (is_dir($confDir . '/routes/')) {
            $routes->import($confDir . '/routes/*' . static::CONFIG_EXTS);
        }

        if (is_dir($confDir . '/routes/' . $this->environment)) {
            $routes->import($confDir . '/routes/' . $this->environment . '/**/*' . static::CONFIG_EXTS);
        }

        $routes->import($confDir . '/routes' . static::CONFIG_EXTS);
    }

    public function getAppEnv(): string
    {
        return $_SERVER['APP_ENV'] ?? 'dev';
    }

    public function getAppVendor(): string
    {
        return $_SERVER['APP_VENDOR'] ?? 'unknown';
    }

    public function getAppName(): string
    {
        return $_SERVER['APP_NAME'] ?? 'unknown';
    }

    public function getAppVersion(): string
    {
        return $_SERVER['APP_VERSION'] ?? 'N.N.N';
    }

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

    public function getAppDeploymentId(): string
    {
        if ($this->isDebug()) {
            return $this->getAppBuild();
        }

        return $_SERVER['APP_DEPLOYMENT_ID'] ?? $this->getAppBuild();
    }

    public function getAppDevBranch(): string
    {
        return $_SERVER['APP_DEV_BRANCH'] ?? 'master';
    }

    public function getSystemMacAddress(): string
    {
        return $_SERVER['SYSTEM_MAC_ADDRESS'] ?? '';
    }

    public function getCloudProvider(): string
    {
        return $_SERVER['CLOUD_PROVIDER'] ?? 'private';
    }

    public function getCloudRegion(): string
    {
        return $_SERVER['CLOUD_REGION'] ?? 'unknown';
    }

    public function getCloudZone(): string
    {
        return $_SERVER['CLOUD_ZONE'] ?? 'unknown';
    }

    public function getCloudInstanceId(): string
    {
        return $_SERVER['CLOUD_INSTANCE_ID'] ?? 'unknown';
    }

    public function getCloudInstanceType(): string
    {
        return $_SERVER['CLOUD_INSTANCE_TYPE'] ?? 'unknown';
    }

    public function getConfigDir(): string
    {
        return $_SERVER['APP_CONFIG_DIR'] ?? $this->getProjectDir() . '/config';
    }

    public function getCacheDir(): string
    {
        return $_SERVER['APP_CACHE_DIR'] ?? $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    public function getLogDir(): string
    {
        return $_SERVER['APP_LOGS_DIR'] ?? $this->getProjectDir() . '/var/logs';
    }

    public function getTmpDir(): string
    {
        return $_SERVER['APP_TMP_DIR'] ?? $this->getProjectDir() . '/var/tmp';
    }

    protected function getKernelParameters(): array
    {
        $parameters = parent::getKernelParameters();
        $parameters['kernel.config_dir'] = $this->getConfigDir();
        if (!isset($parameters['kernel.tmp_dir'])) {
            $parameters['kernel.tmp_dir'] = realpath($this->getTmpDir()) ?: $this->getTmpDir();
        }

        return $parameters;
    }
}
