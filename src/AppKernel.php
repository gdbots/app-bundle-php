<?php

namespace Gdbots\Bundle\AppBundle;

use Symfony\Component\HttpKernel\KernelInterface;

interface AppKernel extends KernelInterface
{
    /**
     * Creator of the application, typically your company name.
     * (if using composer, the first part of the package name before the "/")
     *
     * @return string
     */
    public function getAppVendor();

    /**
     * The name of the application.
     * (if using composer, the second part of the package name after the "/")
     *
     * @return string
     */
    public function getAppName();

    /**
     * The version of the application.  e.g. v1.0.0, git commit hash, svn revision
     * or just a timestamp of when composer install/update was last run.
     *
     * @return string
     */
    public function getAppVersion();

    /**
     * An identifier for the deployment of this app.  This is generally a timestamp
     * formatted as "YmdHis".  NOTE: When in debug mode, the build will be a new
     * timestamp every request.
     *
     * @return string
     */
    public function getAppDeploymentId();

    /**
     * When in development you can optionally define the dev branch that is being
     * used currently which is useful for customizing configuration, logging,
     * gearman channels, etc.  Default is "master".
     *
     * @return string
     */
    public function getAppDevBranch();

    /**
     * Root directory where the application is deployed.
     *
     * @return string
     */
    public function getAppRootDir();

    /**
     * A media access control address (MAC address), also called physical address,
     * is a unique identifier assigned to network interfaces.
     * @link https://en.wikipedia.org/wiki/MAC_address
     *
     * @return string
     */
    public function getSystemMacAddress();

    /**
     * The cloud provider (aws, azure, google, rackspace, etc.)
     *
     * @return string
     */
    public function getCloudProvider();

    /**
     * The region this app is currently running in.  e.g. us-east-1 (aws), us-central1 (google)
     *
     * @return string
     */
    public function getCloudRegion();

    /**
     * The zone (partition of a region) the app is running in.
     *
     * @return string
     */
    public function getCloudZone();

    /**
     * A unique identifier for the instance.
     *
     * @return string
     */
    public function getCloudInstanceId();

    /**
     * A reference to the machine type/size. e.g. on EC2 this might be "c4.xlarge"
     *
     * @return string
     */
    public function getCloudInstanceType();
}