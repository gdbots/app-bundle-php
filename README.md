app-bundle-php
=============

[![Build Status](https://api.travis-ci.org/gdbots/app-bundle-php.svg)](https://travis-ci.org/gdbots/app-bundle-php)
[![Code Climate](https://codeclimate.com/github/gdbots/app-bundle-php/badges/gpa.svg)](https://codeclimate.com/github/gdbots/app-bundle-php)
[![Test Coverage](https://codeclimate.com/github/gdbots/app-bundle-php/badges/coverage.svg)](https://codeclimate.com/github/gdbots/app-bundle-php/coverage)

App bundle for symfony apps which provides a base app kernel, device view and viewer country awareness.


## AppKernel
Provides an AppKernel interface and an `AbstractAppKernel` which must be extended in your own app.  This class provides some basic methods for describing the environment the kernel is running in (cloud provider, region, etc.) and the app details like vendor, package, version, build, etc.


## Console Commands
When an app is deployed we need to execute a symfony command and/or curl the app to verify the deployment was successful.  The console command `console app:describe` can return the app details.

Example output from the command:
```json
{
  "symfony_version": "4.0.0",
  "app_vendor": "acme",
  "app_name": "blog",
  "app_version": "v0.1.0",
  "app_build": "1487902285",
  "app_deployment_id": "d-IHMA71LSM",
  "app_dev_branch": "master",
  "system_mac_address": "02:a0:57:b4:59:e9",
  "cloud_provider": "private",
  "cloud_region": "us-west-2",
  "cloud_zone": "us-west-2a",
  "cloud_instance_id": "080027a450f9",
  "cloud_instance_type": "vbox",
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
if (( $( curl -s --resolve ${app_domain}:8080:127.0.0.1 http://${app_domain}:8080/health-check | grep -c "APP_DEPLOYMENT_ID = '${DEPLOYMENT_ID}'" ) > 0 ))
then
  echo "[${APP_NAME}] validate success (curl)"
else
  echo "[${APP_NAME}] validate failure (curl), does not have [APP_DEPLOYMENT_ID = '${DEPLOYMENT_ID}']"
  exit 1
fi

app_deployment_id=`sudo -H -u ${APP_OWNER} php ${APP_DIR}/bin/console app:describe --env=${APP_ENV} --no-debug --no-interaction | jq -r '.app_deployment_id'`
if [ "${app_deployment_id}" == "${DEPLOYMENT_ID}" ]; then
  echo "[${APP_NAME}] validate success (console)"
else
  echo "[${APP_NAME}] validate failure (console), app returned '${app_deployment_id}', expected '${DEPLOYMENT_ID}'"
  exit 1
fi
```


## Device View Awareness
If device detection is in use and was evaluated for the current request a string identifying the "view" that is to be delivered to the user will be pushed into an environment variable by the server.

This string is completely up to the app developer as what view is given to the user is not necessarily going to match exactly to the form factor of the device.

For example, a smarttv might be shown the "desktop" view of the app.

Examples: desktop, smartphone, smarttv, etc. <https://www.scientiamobile.com/wurflCapability>

This bundle doesn't provide any detection, it merely injects the "decision" into the request attributes and provides some methods to make template resolution simple.  The actual detection is done by a tool better suited for that, like CloudFront.  For example, in an Apache rewrite rule:

```
  RewriteCond %{HTTP:CloudFront-Is-Mobile-Viewer} =true
  RewriteCond %{HTTP:CloudFront-Is-Tablet-Viewer} =false
  RewriteRule ^ - [E=DEVICE_VIEW:smartphone]
```

The "device_view" would now equal "smartphone" and be available as a twig variable, a request attribute or an environment variable.  Example use in a controller:

```php
declare(strict_types=1);

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
Using [Twig dynamic inheritance](http://twig.sensiolabs.org/doc/2.x/tags/extends.html#dynamic-inheritance) you can make use of the `device_view` variable to provide a device specific layout when available.
```twig
{% extends ['layout.' ~ device_view ~ '.twig.html', 'layout.twig.html'] %}

I'm on a {{ device_view }}.
```


## Viewer Country Awareness
Using the same strategy as device view, this value contains the viewer's country.  This string should be a two digit ISO country code, all uppercase or null.

This bundle doesn't provide any detection, it merely injects the "decision" into the request attributes and provides some methods to make template resolution simple.  The actual detection is done by a tool better suited for that, like CloudFront.  For example, in an Apache rewrite rule:

```
  RewriteCond %{HTTP:CloudFront-Viewer-Country} ([A-Z0-9]{2})
  RewriteRule ^ - [E=VIEWER_COUNTRY:%1]
```
You can reference the value in Symfony request with `$request->attributes->get('viewer_country')` or in twig with `I'm in {{ viewer_country }}.`
