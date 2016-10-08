<?php
require_once dirname(__FILE__) . '/../loft_core.module';
require_once dirname(__FILE__) . '/../../data_api/tests/bootstrap.php';

function t($string, $vars = array())
{
    array_walk($vars, function ($replace, $find) use (&$string) {
        $string = str_replace($find, $replace, $string);
    });

    return $string;
}
