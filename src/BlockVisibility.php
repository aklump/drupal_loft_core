<?php

/**
 * Class BlockVisibility
 *
 * Advanced block visibility for Drupal.
 */
class BlockVisibility {

  public $default, $path;

  /**
   * BlockVisibility constructor.
   *
   * @param bool $default Optional, to set the default visibility of the block.
   * @param null $path    Optional, to inject the path for the current
   *                      page/test.
   * @param null $alias   Optional, to inject the alias for the current
   *                      page/test.
   */
  public function __construct($path = NULL, $alias = NULL) {
    $this->default = NULL;
    $this->path = isset($path) ? $path : current_path();
    $this->alias = isset($alias) ? $alias : drupal_get_path_alias();
    $this->results = array();
  }

  public function show_if_alias() {
    $this->setDefault(0);
    $args = func_get_args();
    foreach ($args as $arg) {
      if ($arg === $this->alias) {
        $this->results[] = TRUE;
        break;
      }
    }
  }

  private function setDefault($default) {
    if (is_null($this->default)) {
      $this->default = (boolean) $default;
    }
  }

  public function hide_if_alias() {
    $this->setDefault(1);
    $args = func_get_args();
    foreach ($args as $arg) {
      if ($arg === $this->alias) {
        $this->results[] = FALSE;
        break;
      }
    }
  }

  public function show_if_nid() {
    $this->setDefault(0);
    list($node, $nid) = explode('/', $this->path) + array(NULL, NULL);
    if ($node !== 'node' || empty($nid)) {
      return;
    }
    $args = func_get_args();
    foreach ($args as $arg) {
      if ($arg == $nid) {
        $this->results[] = TRUE;
        break;
      }
    }
  }

  public function hide_if_nid() {
    $this->setDefault(1);
    list($node, $nid) = explode('/', $this->path) + array(NULL, NULL);
    if ($node !== 'node' || empty($nid)) {
      return;
    }
    $args = func_get_args();
    foreach ($args as $arg) {
      if ($arg == $nid) {
        $this->results[] = FALSE;
        break;
      }
    }
  }

  public function show_if_alias_regex($regex) {
    $this->setDefault(0);
    if (preg_match($regex, $this->alias)) {
      $this->results[] = TRUE;
    }
  }

  public function hide_if_alias_regex($regex) {
    $this->setDefault(1);
    if (preg_match($regex, $this->alias)) {
      $this->results[] = FALSE;
    }
  }

  public function isVisible() {
    $result = count($this->results) ? reset($this->results) : $this->default;

    return (bool) $result;
  }
}
