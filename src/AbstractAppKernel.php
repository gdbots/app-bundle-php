<?php

namespace Gdbots\Bundle\AppBundle;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Using this class assumes the use of @see Gdbots\Bundle\AppBundle\Composer::installConstantsFile
 * and having it run in the "pre-install-cmd" and "pre-update-cmd" composer event hooks.
 *
 * The constants written to your project's document root (by default into .constants.php) are then
 * included, ideally via composer "files" before any of your code runs.
 */
abstract class AbstractAppKernel extends Kernel implements AppKernel
{
    protected $appBuild;

    /**
     * @param LoaderInterface $loader
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }

    /**
     * @return string
     */
    public function getAppVendor()
    {
        return APP_VENDOR;
    }

    /**
     * @return string
     */
    public function getAppName()
    {
        return APP_NAME;
    }

    /**
     * @return string
     */
    public function getAppVersion()
    {
        return APP_VERSION;
    }

    /**
     * @return string
     */
    public function getAppBuild()
    {
        if ($this->isDebug()) {
            $this->appBuild = explode('.', $this->getStartTime())[0];
        } else {
            $this->appBuild = APP_BUILD;
        }

        return $this->appBuild;
    }

    /**
     * @return string
     */
    public function getAppDevBranch()
    {
        return APP_DEV_BRANCH;
    }

    /**
     * @return string
     */
    public function getAppRootDir()
    {
        return APP_ROOT_DIR;
    }

    /**
     * @return string
     */
    public function getSystemMacAddress()
    {
        return SYSTEM_MAC_ADDRESS;
    }

    /**
     * @return string
     */
    public function getCloudProvider()
    {
        return CLOUD_PROVIDER;
    }

    /**
     * @return string
     */
    public function getCloudRegion()
    {
        return CLOUD_REGION;
    }

    /**
     * @return string
     */
    public function getCloudZone()
    {
        return CLOUD_ZONE;
    }

    /**
     * @return string
     */
    public function getCloudInstanceId()
    {
        return CLOUD_INSTANCE_ID ?: $this->getSystemMacAddress();
    }

    /**
     * @return string
     */
    public function getCloudInstanceType()
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
        return $this->getAppRootDir().'/app';
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->getAppRootDir().'/var/cache/'.$this->environment;
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return $this->getAppRootDir().'/var/logs/'.$this->environment;
    }

    /**
     * @return string
     */
    public function getTmpDir()
    {
        return $this->getAppRootDir().'/var/tmp/'.$this->environment;
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
        $parameters['app_dev_branch'] = $this->getAppDevBranch();
        $parameters['app_root_dir'] = $this->getAppRootDir();
        $parameters['system_mac_address'] = $this->getSystemMacAddress();
        $parameters['cloud_provider'] = $this->getCloudProvider();
        $parameters['cloud_region'] = $this->getCloudRegion();
        $parameters['cloud_zone'] = $this->getCloudZone();
        $parameters['cloud_instance_id'] = $this->getCloudInstanceId();
        $parameters['cloud_instance_type'] = $this->getCloudInstanceType();
        $parameters['kernel.tmp_dir'] = $this->getTmpDir();

        // convenient flags for environments
        $env = strtolower(trim($this->environment));
        $parameters['is_'.$env.'_environment'] = true;
        $parameters['is_production'] = 'prod' === $env || 'production' === $env ? true : false;
        $parameters['is_not_production'] = !$parameters['is_production'];

        return $parameters;
    }
}
