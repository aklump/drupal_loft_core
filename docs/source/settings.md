# Two new constants

## Files used

    settings.env.php
    settings.dev.php
    settings.prod.php
    settings.local.php
    
## Changes to `settings.php`
    
1. Add the following lines to `settings.php`:
    
        require dirname(__FILE__) . '/settings.env.php';
        require dirname(__FILE__) . '/settings.' . DRUPAL_ENV . '.php';
        require dirname(__FILE__) . '/settings.local.php';
    
1. Create a file `settings.env.php` with this:

        <?php
        /**
         * @var $settings_presets
         * Define the environment: dev or prod
         *
         * prod:
         * - will server minified js
         * - will enable the prod settings presets file
         */
        define('DRUPAL_ENV', 'dev');
        
1. Create `settings.dev.php` and `settings.prod.php` and put in the environment settings specific to environment, e.g. cache settings.
1. Move the database declaration into `settings.local.php`.  Also include the following lines:

        //
        //
        // Define the server environment's role, one of: prod, staging, dev
        //
        // The role may be different than the environment, for example a staging server should have a production environment, but it is serving a staging role.  Production would have the same 'prod' for both DRUPAL_ENV_ROLE and DRUPAL_ENV.
        //
        define('DRUPAL_ENV_ROLE', 'dev');

## Available in PHP as

    DRUPAL_ENV
    DRUPAL_ENV_ROLE

## Available in JS as
    
    Drupal.settings.DRUPAL_ENV
    Drupal.settings.DRUPAL_ENV_ROLE
