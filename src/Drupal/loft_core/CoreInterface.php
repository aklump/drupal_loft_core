<?php


namespace Drupal\loft_core;


interface CoreInterface {

  /**
   * Return a callable or a text format to use as a fallback when handling safe markup.
   *
   * @return string|callable
   */
  public function getSafeMarkupHandler();
}
