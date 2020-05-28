# CHANGELOG for 2.x
This changelog references the relevant changes done in 2.x versions.


## v2.0.0
__BREAKING CHANGES__

* Upgrade to support Symfony 5 and PHP 7.4.
* Rename event listeners to `EventSubscriber\*Subscriber`.
* Add `gdpr_applies` twig function.
* Make commands lazy by using the symfony static name.