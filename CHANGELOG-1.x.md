# CHANGELOG for 1.x
This changelog references the relevant changes done in 1.x versions.


## v1.0.3
* Add `app:compile-twig-templates` command so no requests have to do the twig compiling (prevent the thundering herd on opcache and twig compiliation).


## v1.0.2
* Set `secure` attribute for `device_view` cookie in `DeviceViewListener` based on the `request` itself.


## v1.0.1
* Use `SameSite=strict` for device_view cookie in `DeviceViewListener`.


## v1.0.0
* Initial stable version.
