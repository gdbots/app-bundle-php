app-bundle-php
=============

[![Build Status](https://api.travis-ci.org/gdbots/app-bundle-php.svg)](https://travis-ci.org/gdbots/app-bundle-php)
[![Code Climate](https://codeclimate.com/github/gdbots/app-bundle-php/badges/gpa.svg)](https://codeclimate.com/github/gdbots/app-bundle-php)
[![Test Coverage](https://codeclimate.com/github/gdbots/app-bundle-php/badges/coverage.svg)](https://codeclimate.com/github/gdbots/app-bundle-php/coverage)

App bundle for symfony apps which provides a base app kernel, device view awareness and a composer script
handler to produce a constants file with app details.


## AppKernel
Provides an AppKernel interface and an `AbstractAppKernel` which must be extended in your own app.  This
class provides some basic methods for describing the environment the kernel is running in (cloud provider, region, etc.)
and the app details like vendor, package, version, build, etc.

The kernel also injects that data into the kernel parameters:

```php
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
```
These can then be used in symfony app configs:

```yaml
  my_bucket: 'https://s3-%cloud_region%.amazonaws.com/my-bucket-%kernel.environment%-%cloud_region%'
```

Why not use environment variables for all of this?  In our use case, we generate the a `.constants.php` file
using the composer `ScriptHandler` which has application details which are generated at build or deploy time
and then would not change unless a new deploy happened.

This happens when composer install runs or potentially Chef or CodeDeploy.  Composer json example:

```json
{
  "scripts": {
    "install-parameters": "php -r \"if (file_exists('app/config/parameters.yml')) exit; copy('app/config/parameters.yml.dist', 'app/config/parameters.yml');\"",
    "symfony-scripts": [
      "@install-parameters",
      "Gdbots\\Bundle\\AppBundle\\Composer\\ScriptHandler::installConstantsFile",
      "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::buildBootstrap",
      "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installRequirementsFile"
    ],
    "post-install-cmd": [
      "@symfony-scripts"
    ],
    "post-update-cmd": [
      "@symfony-scripts"
    ],
    "test": "vendor/bin/phpunit"
  }
}
```

CodeDeploy provisioning script example:
```bash
sed -i "/APP_BUILD/s/'[^']*'/'${DEPLOYMENT_ID}'/2" .constants.php
sed -i "/APP_DEV_BRANCH/s/'[^']*'/'${APP_BRANCH}'/2" .constants.php
sed -i "/SYSTEM_MAC_ADDRESS/s/'[^']*'/'${SYSTEM_MAC_ADDRESS}'/2" .constants.php
sed -i "/CLOUD_PROVIDER/s/'[^']*'/'${CLOUD_PROVIDER}'/2" .constants.php
sed -i "/CLOUD_REGION/s/'[^']*'/'${CLOUD_REGION}'/2" .constants.php
sed -i "/CLOUD_ZONE/s/'[^']*'/'${CLOUD_ZONE}'/2" .constants.php
sed -i "/CLOUD_INSTANCE_ID/s/'[^']*'/'${CLOUD_INSTANCE_ID}'/2" .constants.php
sed -i "/CLOUD_INSTANCE_TYPE/s/'[^']*'/'${CLOUD_INSTANCE_TYPE}'/2" .constants.php
```


## Console Commands
When an app is deployed we need to execute a symfony command and/or curl the app to verify
the deployment was successful.  The console command `console app:describe` can return the app details.

Example output from the command:
```json
{
  "symfony_version": "3.2.3",
  "app_vendor": "acme",
  "app_name": "blog",
  "app_version": "v0.1.0",
  "app_build": "1487902285",
  "app_dev_branch": "master",
  "system_mac_address": "02:a0:57:b4:59:e9",
  "cloud_provider": "private",
  "cloud_region": "us-west-2",
  "cloud_zone": "us-west-2a",
  "cloud_instance_id": "080027a450f9",
  "cloud_instance_type": "vbox",
  "is_production": false,
  "is_not_production": true,
  "kernel_environment": "local",
  "kernel_debug": true,
  "kernel_bundles": {
    "FrameworkBundle": "Symfony\\Bundle\\FrameworkBundle\\FrameworkBundle",
    "MonologBundle": "Symfony\\Bundle\\MonologBundle\\MonologBundle",
    "SecurityBundle": "Symfony\\Bundle\\SecurityBundle\\SecurityBundle",
    "TwigBundle": "Symfony\\Bundle\\TwigBundle\\TwigBundle",
    "SensioFrameworkExtraBundle": "Sensio\\Bundle\\FrameworkExtraBundle\\SensioFrameworkExtraBundle",
    "AwsBundle": "Aws\\Symfony\\AwsBundle",
    "GdbotsAppBundle": "Gdbots\\Bundle\\AppBundle\\GdbotsAppBundle",
    "AppBundle": "AppBundle\\AppBundle",
    "DebugBundle": "Symfony\\Bundle\\DebugBundle\\DebugBundle"
  }
}
```

Example use in CodeDeploy ValidateService hook:
```bash
if (( $( curl -s --resolve ${app_domain}:8080:127.0.0.1 http://${app_domain}:8080/health-check | grep -c "APP_BUILD = '${DEPLOYMENT_ID}'" ) > 0 ))
then
  echo "[${APP_NAME}] validate success (curl)"
else
  echo "[${APP_NAME}] validate failure (curl), does not have [APP_BUILD = '${DEPLOYMENT_ID}']"
  exit 1
fi

app_build=`sudo -H -u ${APP_OWNER} php ${APP_DIR}/bin/console app:describe --env=${APP_ENV} --no-debug --no-interaction | jq -r '.app_build'`
if [ "${app_build}" == "${DEPLOYMENT_ID}" ]; then
  echo "[${APP_NAME}] validate success (console)"
else
  echo "[${APP_NAME}] validate failure (console), app returned '${app_build}', expected '${DEPLOYMENT_ID}'"
  exit 1
fi
```


## Device View Awareness
If device detection is in use and was evaluated for the current request a string identifying the 
"view" that is to be delivered to the user will be pushed into an environment variable by the server.

This string is completely up to the app developer as what view is given to the user is not necessarily 
going to match exactly to the form factor of the device.

For example, a smarttv might be shown the "desktop" view of the app.

Examples: desktop, smartphone, smarttv, etc. <https://www.scientiamobile.com/wurflCapability>

This bundle doesn't provide any detection, it merely injects the "decision" into the request attributes
and provides some methods to make template resolution simple.  The actual detection is done by a tool
better suited for that, like CloudFront.  For example, in an Apache rewrite rule:

```
  RewriteCond %{HTTP:CloudFront-Is-Mobile-Viewer} =true
  RewriteCond %{HTTP:CloudFront-Is-Tablet-Viewer} =false
  RewriteRule ^ - [E=DEVICE_VIEW:smartphone]
```

The "device_view" would now equal "smartphone" and be available as a twig variable, a request attribute or
and environment variable.  Example use in a controller:

```php
declare(strict_types = 1);

namespace AppBundle\Controller;

use Gdbots\Bundle\AppBundle\Controller\DeviceViewRendererTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

final class DefaultController extends Controller
{
    use DeviceViewRendererTrait;

    /**
     * @Route("/")
     *
     * @return Response
     */
    public function indexAction(): Response
    {
        // if device_view is populated and the template index.smartphone.html.twig exists, it will be used
        // otherwise the "index.html.twig" file will be loaded.
        return $this->renderUsingDeviceView('@app/index%device_view%.html.twig');
    }
}
```
