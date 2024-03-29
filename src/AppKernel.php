<?php
declare(strict_types=1);

namespace Gdbots\Bundle\AppBundle;

use Symfony\Component\HttpKernel\KernelInterface;

interface AppKernel extends KernelInterface
{
    /**
     * The application environment we are running. This is different than
     * symfony env or kernel environment though they may seem the same.
     * The reason for this is that when we generate a docker image for
     * symfony we want to prime the cache so bootup is super fast.  The
     * only way to do that is by knowing the "environment" ahead of time,
     * this of course is difficult when you want to reuse the same image
     * across many environments. The solution is to use symfony environment
     * as a "distribution" or "provider", like "aws", "gce".
     *
     * tldr;
     *  - app_env = dev, test, stage, prod, etc.
     *  - kernel/symfony env = aws, private, gce, azure
     *
     * @return string
     */
    public function getAppEnv(): string;

    /**
     * Creator of the application, typically your company name.
     * (if using composer, the first part of the package name before the "/")
     *
     * @return string
     */
    public function getAppVendor(): string;

    /**
     * The name of the application.
     * (if using composer, the second part of the package name after the "/")
     *
     * @return string
     */
    public function getAppName(): string;

    /**
     * The version of the application.  e.g. v1.0.0, git commit hash, svn revision
     * or just a timestamp of when composer install/update was last run.
     *
     * @return string
     */
    public function getAppVersion(): string;

    /**
     * An identifier for the build of this app.  This is generally a timestamp
     * formatted as "YmdHis".  NOTE: When in debug mode, the build will be a new
     * timestamp every request.
     *
     * @return string
     */
    public function getAppBuild(): string;

    /**
     * An identifier for the deployment of this app.  This may be the same as the
     * app build if it's not supported.  For example, AWS CodeDeploy provides a
     * deployment id which is different from when the app was built/compiled.
     *
     * @return string
     */
    public function getAppDeploymentId(): string;

    /**
     * When in development you can optionally define the dev branch that is being
     * used currently which is useful for customizing configuration, logging,
     * gearman channels, etc.  Default is "master".
     *
     * @return string
     */
    public function getAppDevBranch(): string;

    /**
     * A media access control address (MAC address), also called physical address,
     * is a unique identifier assigned to network interfaces.
     * @link https://en.wikipedia.org/wiki/MAC_address
     *
     * @return string
     */
    public function getSystemMacAddress(): string;

    /**
     * The cloud provider (aws, azure, google, rackspace, etc.)
     *
     * @return string
     */
    public function getCloudProvider(): string;

    /**
     * The region this app is currently running in.  e.g. us-east-1 (aws), us-central1 (google)
     *
     * @return string
     */
    public function getCloudRegion(): string;

    /**
     * The zone (partition of a region) the app is running in.
     *
     * @return string
     */
    public function getCloudZone(): string;

    /**
     * A unique identifier for the instance.
     *
     * @return string
     */
    public function getCloudInstanceId(): string;

    /**
     * A reference to the machine type/size. e.g. on EC2 this might be "c4.xlarge"
     *
     * @return string
     */
    public function getCloudInstanceType(): string;

    /**
     * Gets the project's config directory.
     *
     * @return string
     */
    public function getConfigDir(): string;

    /**
     * Gets the project's tmp directory.
     *
     * @return string
     */
    public function getTmpDir(): string;
}
