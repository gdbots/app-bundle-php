# CHANGELOG for 0.x
This changelog references the relevant changes done in 0.x versions.


## v0.3.0
__BREAKING CHANGES__

* Add support for Symfony 4.
* Mark all classes as final since this library is not intended to be extended 
  except for the use of the `AbstractAppKernel` and `DeviceViewRendererTrait`.


## v0.2.0
__BREAKING CHANGES__

* Make `AbstractAppKernel` use symfony's new `kernel.project_dir`.
* issue #2: Derive app version from composer version instead of custom `extra['gdbots-app'].version`
* issue #3: Add `app_deployment_id` to constants and kernel. 


## v0.1.0
* Initial version.
