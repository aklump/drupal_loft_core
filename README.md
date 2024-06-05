# Loft Core

![Loft Core](images/loft_core.jpg)

This module contains features that I wish were in core, and which I often use for all my projects. Documentation can be found at _docs/index.html_.

##   Install with Composer

1. Because this is an unpublished package, you must define it's repository in
   your project's _composer.json_ file. Add the following to _composer.json_ in
   the `repositories` array:
   
    ```json
    {
        "type": "github",
        "url": "https://github.com/aklump/drupal_loft_core"
    }
    ```
1. Require this package:
   
    ```
    composer require aklump_drupal/loft_core:^3.0
    ```
1. Add the installed directory to _.gitignore_
   
   ```php
   /web/modules/custom/loft_core/
   ```

1. Enable this module.
