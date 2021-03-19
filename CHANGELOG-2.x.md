# CHANGELOG for 2.x
This changelog references the relevant changes done in 2.x versions.


## v2.1.1
* Check for LoaderError exception direct or in previous for `DeviceViewRendererTrait::renderUsingDeviceView` fallback.


## v2.1.0
* Require `"symfony/console": "^5.1"`
* Require `"symfony/framework-bundle": "^5.1"`
* Use new symfony bundle structure https://github.com/symfony/symfony/blob/master/UPGRADE-5.0.md


## v2.0.0
__BREAKING CHANGES__

* Upgrade to support Symfony 5 and PHP 7.4.
* Rename event listeners to `EventSubscriber\*Subscriber`.
* Add `gdpr_applies` twig function.
* Make commands lazy by using the symfony static name.
