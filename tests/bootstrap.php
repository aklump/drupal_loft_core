<?php
require_once dirname(__FILE__) . '/../vendor/autoload.php';

define('DRUPAL_ENV_ROLE', 'staging');
require_once dirname(__FILE__) . '/../loft_core.module';
require_once dirname(__FILE__) . '/../modules/loft_core_users/loft_core_users.module';
require_once dirname(__FILE__) . '/../../d8now/vendor/autoload.php';
require_once dirname(__FILE__) . '/../../data_api/tests/bootstrap.php';

function t($string, $vars = array())
{
    array_walk($vars, function ($replace, $find) use (&$string) {
        $string = str_replace($find, $replace, $string);
    });

    return $string;
}

function check_plain($a)
{
    return $a;
}
