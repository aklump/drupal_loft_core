<?php


namespace Drupal\loft_core;


interface CoreInterface {

  /**
   * Return a callable or a text format to use as a fallback when handling safe markup.
   *
   * Callables and functions will be given the $value as the argument.  A good default value is 'filter_xss'.
   *
   * @return string|callable
   *
   * @see filter_formats()
   */
  public function getSafeMarkupHandler();
}
