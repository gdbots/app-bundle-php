<?php

namespace Gdbots\Symfony\Kernel;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

abstract class AbstractAppKernel extends Kernel
{
    /**
     * The name of the system.
     *
     * @var string
     */
    protected $systemName;

    /**
     * The version of the system.
     *
     * @var string
     */
    protected $systemVersion;

    /**
     * The current node identifer, usually mac address or instance UUID.
     *
     * @var string
     */
    protected $systemNode;

    /**
     * The real path to the system (filepath)
     *
     * @var string
     */
    protected $systemRootDir;

    /**
     * The context of the kernel.  (api, cms, www)
     *
     * @var string
     */
    protected $systemContext;

    /**
     * The cloud environment (AWS, AZURE, RACKSPACE, etc.)
     *
     * @var string
     */
    protected $cloudEnvironment;

    /**
     * Constructor.
     *
     * @param string  $environment The environment
     * @param Boolean $debug       Whether to enable debugging or not
     *
     * @api
     */
    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);
        $this->getSystemRootDir();
        $this->getSystemContext();
    }

    /**
     * @param LoaderInterface $loader
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getSystemRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function getName()
    {
        if (null === $this->name) {
            $this->name = ucfirst($this->getSystemName()) . ucfirst($this->getSystemContext());
        }

        return $this->name;
    }

    /**
     * Returns the name of the system that this app is
     * apart of.  i.e. piloxing
     *
     * currently expects SYSTEM_NAME to be defined
     */
    public function getSystemName()
    {
        if (null === $this->systemName) {
            $this->systemName = SYSTEM_NAME;
        }

        return $this->systemName;
    }

    /**
     * Returns the version of the system.
     *
     * currently expects SYSTEM_VERSION to be defined
     */
    public function getSystemVersion()
    {
        if (null === $this->systemVersion) {
            $this->systemVersion = SYSTEM_VERSION;
        }

        return $this->systemVersion;
    }

    /**
     * Returns the node of the system.
     *
     * currently expects SYSTEM_NODE to be defined
     */
    public function getSystemNode()
    {
        if (null === $this->systemNode) {
            $this->systemNode = SYSTEM_NODE;
        }

        return $this->systemNode;
    }

    /**
     * Returns the parent most directory where this collection of
     * symfony apps are stored. i.e. the "system"
     *
     * currently expects SYSTEM_ROOT to be defined
     */
    public function getSystemRootDir()
    {
        if (null === $this->systemRootDir) {
            $this->systemRootDir = SYSTEM_ROOT;
        }

        return $this->systemRootDir;
    }

    /**
     * Returns the context of this app kernel in the system.
     * The "purpose" of this app kernel.  cms, api, www, etc.
     */
    public function getSystemContext()
    {
        if (null === $this->systemContext) {
            $pathParts = explode(DIRECTORY_SEPARATOR, $this->getRootDir());
            end($pathParts);
            $this->systemContext = prev($pathParts);
        }

        return $this->systemContext;
    }

    /**
     * Returns the cloud environment we're currently
     * running in.
     */
    public function getCloudEnvironment()
    {
        if (null === $this->cloudEnvironment) {
            $this->cloudEnvironment = getenv('CLOUD_ENV') ?: 'private';
        }

        return $this->cloudEnvironment;
    }

    /**
     * When running on a local Vagrant VM the cache dir
     * is fixed at /tmp/cache/%systemName%/%systemContext%-%env%/
     *
     * {@inheritdoc}
     *
     * @api
     */
    public function getCacheDir()
    {
        if ('local' === $this->environment) {
            //return "/tmp/{$this->systemName}/cache/{$this->systemContext}-{$this->environment}";
        }

        return "{$this->systemRootDir}/cache/{$this->systemContext}-{$this->environment}";
    }

    /**
     * When running on a local Vagrant VM the logs dir
     * is fixed at /tmp/logs/%systemName%/%systemContext%-%env%/
     *
     * {@inheritdoc}
     *
     * @api
     */
    public function getLogDir()
    {
        if ('local' === $this->environment) {
            //return "/tmp/{$this->systemName}/logs/{$this->systemContext}-{$this->environment}";
        }

        return "{$this->systemRootDir}/logs/{$this->systemContext}-{$this->environment}";
    }

    /**
     * When running on a local Vagrant VM the tmp dir
     * is fixed at /tmp/tmp/%systemName%/%systemContext%-%env%/
     *
     * {@inheritdoc}
     *
     * @api
     */
    public function getTmpDir()
    {
        if ('local' === $this->environment) {
            //return "/tmp/{$this->systemName}/logs/{$this->systemContext}-{$this->environment}";
        }

        return "{$this->systemRootDir}/tmp/{$this->systemContext}-{$this->environment}";
    }

    /**
     * Calls parent to get builtin kernel parameters and
     * then adds a few key settings.
     *
     * @return array An array of kernel parameters
     */
    protected function getKernelParameters()
    {
        $parameters = parent::getKernelParameters();
        $parameters['system.version'] = $this->getSystemVersion();
        $parameters['system.node'] = $this->getSystemNode();
        $parameters['system.root_dir'] = $this->getSystemRootDir();
        $parameters['kernel.tmp_dir'] = $this->getTmpDir();
        $parameters['system.context'] = $this->getSystemContext();
        $parameters['system.cloud'] = $this->getCloudEnvironment();
        return $parameters;
    }
}
