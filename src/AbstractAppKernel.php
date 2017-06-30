<?php
declare(strict_types=1);

namespace Gdbots\Bundle\AppBundle;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Using this class assumes the use of @see \Gdbots\Bundle\AppBundle\Composer\ScriptHandler::installConstantsFile
 * and having it run in the "post-install-cmd" and "post-update-cmd" composer event hooks.
 *
 * The constants written to your project's document root (by default into .constants.php) are then
 * included, ideally via composer "files" before any of your code runs.
 */
abstract class AbstractAppKernel extends Kernel implements AppKernel
{
    /** @var string */
    protected $appBuild;

    /**
     * @param LoaderInterface $loader
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
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
    public function getAppRootDir(): string
    {
        return APP_ROOT_DIR;
    }

    /**
     * {@inheritdoc}
     */
    public function getProjectDir(): string
    {
        return APP_ROOT_DIR;
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
     * @return string
     */
    public function getName()
    {
        if (null === $this->name) {
            $this->name = str_replace(['_', '\\', 'Kernel'], '', static::class);
        }

        return $this->name;
    }

    /**
     * Assumes your app is in the "app" dir.  If you're running multiple kernels in different
     * folders then you'll need to override this in your concrete kernel.
     *
     * But, in general, it's advised to use the standard app folder structure.
     *
     * @return string
     */
    public function getRootDir()
    {
        return $this->getProjectDir() . '/app';
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        // ensure override is used if present
        $dir = getenv('APP_CACHE_DIR');
        if ($dir) {
            return $dir;
        }

        return $this->getProjectDir() . '/var/cache/' . $this->environment;
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        // ensure override is used if present
        $dir = getenv('APP_LOGS_DIR');
        if ($dir) {
            return $dir;
        }

        return $this->getProjectDir() . '/var/logs';
    }

    /**
     * @return string
     */
    public function getTmpDir(): string
    {
        return $this->getProjectDir() . '/var/tmp';
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
        $parameters['app_root_dir'] = $this->getAppRootDir();
        $parameters['system_mac_address'] = $this->getSystemMacAddress();
        $parameters['cloud_provider'] = $this->getCloudProvider();
        $parameters['cloud_region'] = $this->getCloudRegion();
        $parameters['cloud_zone'] = $this->getCloudZone();
        $parameters['cloud_instance_id'] = $this->getCloudInstanceId();
        $parameters['cloud_instance_type'] = $this->getCloudInstanceType();

        if (!isset($parameters['kernel.tmp_dir'])) {
            $parameters['kernel.tmp_dir'] = realpath($this->getTmpDir()) ?: $this->getTmpDir();
        }

        // convenient flags for environments
        $env = strtolower(trim($this->environment));
        $parameters['is_' . $env . '_environment'] = true;
        $parameters['is_production'] = 'prod' === $env || 'production' === $env ? true : false;
        $parameters['is_not_production'] = !$parameters['is_production'];

        return $parameters;
    }
}
