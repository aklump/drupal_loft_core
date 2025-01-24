# Loft Core

![Loft Core](images/loft_core.jpg)

This module contains features that I wish were in core, and which I often use for all my projects. Documentation can be found at _docs/index.html_.

## Install with Composer

Because this is an unpublished, custom Drupal module, the way you install and depend on it is a little different than published, contributed modules.

* Add the following to the **root-level** _composer.json_ in the `repositories` array:
    ```json
    {
     "type": "github",
     "url": "https://github.com/aklump/drupal_loft_core"
    }
    ```
* Add the installed directory to **root-level** _.gitignore_
  
   ```php
   /web/modules/custom/loft_core/
   ```
* Proceed to either A or B, but not both.
---
### A. Install Standalone
* Require _loft_core_ at the **root-level**.
    ```
    composer require aklump_drupal/loft_core:^3.0
    ```
---
### B. Depend on This Module

(_Replace `my_module` below with your module (or theme's) real name._)

* Add the following to _my_module/composer.json_ in the `repositories` array. (_Yes, this is done both here and at the root-level._)
    ```json
    {
     "type": "github",
     "url": "https://github.com/aklump/drupal_loft_core"
    }
    ```
* From the depending module (or theme) directory run:
    ```
    composer require aklump_drupal/loft_core:^3.0 --no-update
    ```

* Add the following to _my_module.info.yml_ in the `dependencies` array:
    ```yaml
    aklump_drupal:loft_core
    ```
* Back at the **root-level** run `composer update vendor/my_module`


---
### Enable This Module

* Re-build Drupal caches, if necessary.
* Enable this module, e.g.,
  ```shell
  drush pm-install loft_core
  ```

1. Enable this module.
