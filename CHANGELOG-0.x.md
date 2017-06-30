# CHANGELOG for 0.x
This changelog references the relevant changes done in 0.x versions.


## v0.2.0
__BREAKING CHANGES__

* Remove `app_root_dir` and use symfony's new `kernel.project_dir` instead.
* issue #2: Derive app version from composer version instead of custom `extra['gdbots-app'].version`
* issue #3: Add "app deployment id" to constants and kernel. 


## v0.1.0
* Initial version.
