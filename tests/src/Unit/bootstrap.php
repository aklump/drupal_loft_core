<?php

/**
 * @file
 * This file must be autoloaded before running tests.
 */

($value = getenv('DRUPAL_ENV')) && define('DRUPAL_ENV', $value);
($value = getenv('DRUPAL_ENV_ROLE')) && define('DRUPAL_ENV_ROLE', $value);
