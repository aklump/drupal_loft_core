<?php


namespace Drupal\loft_core;


interface CoreInterface {

  /**
   * Return the machine name of the text format to use for safe markup when not otherwise specified.
   *
   * @return string
   */
  public function getSafeMarkupFormat();
}
