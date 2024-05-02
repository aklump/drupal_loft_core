<!--
id: settings
tags: ''
-->

# settings.php

## Files used

    settings.env.php
    settings.dev.php
    settings.prod.php
    settings.local.php

## Changes to `settings.php`

If using environment variables then the following does not apply.

1. Add the following lines to `settings.php` at the very end:

        define('DRUPAL_ENV_PROD', 'prod');
        define('DRUPAL_ENV_STAGING', 'staging');
        define('DRUPAL_ENV_DEV', 'dev');
        require dirname(__FILE__) . '/settings.env.php';
        require dirname(__FILE__) . '/settings.' . DRUPAL_ENV . '.php';
        require dirname(__FILE__) . '/settings.local.php';

1. Create a file `settings.env.php` with this:

        <?php
        /**
         * @var $settings_presets
         * Define the environment: DRUPAL_ENV_DEV or DRUPAL_ENV_PROD
         *
         * prod:
         * - will server minified js
         * - will enable the prod settings presets file
         */
        define('DRUPAL_ENV', DRUPAL_ENV_DEV);

1. Create `settings.dev.php` and `settings.prod.php` and put in the environment settings specific to environment, e.g. cache settings.
1. Move the database declaration into `settings.local.php`.
1. If you're using loft_deploy module you must add a `$conf` var right below the `DRUPAL_ENV` definition.

        $conf['loft_deploy_site_role'] = DRUPAL_ENV;

## Available in PHP as

    DRUPAL_ENV
    DRUPAL_ENV_PROD
    DRUPAL_ENV_STAGING
    DRUPAL_ENV_DEV

## Available in JS as

There must be at least on library using the dependeny of _core/drupalSettings_ for these to appear on the page.

    drupalSettings.env.env

    drupalSettings.env.prod
    drupalSettings.env.staging
    drupalSettings.env.dev
