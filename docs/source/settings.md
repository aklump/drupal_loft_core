# settings.php

## Files used

    settings.env.php
    settings.dev.php
    settings.prod.php
    settings.local.php
    
## Changes to `settings.php`
    
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
1. Move the database declaration into `settings.local.php`.  Also include the following lines:

        //
        //
        // Define the server environment's role, one of: DRUPAL_ENV_PROD, DRUPAL_ENV_STAGING, DRUPAL_ENV_DEV
        //
        // The role may be different than the environment, for example a staging server should have a production environment, but it is serving a staging role.  Production would have the same DRUPAL_ENV_PROD for both DRUPAL_ENV_ROLE and DRUPAL_ENV.
        //
        define('DRUPAL_ENV_ROLE', DRUPAL_ENV_DEV);

1. If you're using loft_deploy module you must add a `$conf` var right below the `DRUPAL_ENV_ROLE` definition. 

        define('DRUPAL_ENV_ROLE', DRUPAL_ENV_DEV);
        $conf['loft_deploy_site_role'] = DRUPAL_ENV_ROLE;

## Available in PHP as

    DRUPAL_ENV
    DRUPAL_ENV_ROLE
    
    DRUPAL_ENV_PROD
    DRUPAL_ENV_STAGING
    DRUPAL_ENV_DEV
    
## Available in JS as
    
    Drupal.settings.DrupalEnv
    Drupal.settings.DrupalEnvRole

    Drupal.settings.DrupalEnvProd
    Drupal.settings.DrupalEnvStaging
    Drupal.settings.DrupalEnvDev
