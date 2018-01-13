<?php
namespace Drupal\loft_core\Entity;

trait ExtractorTrait {

  public function __call($name, $arguments) {

    //
    //
    // A generic fallback for *Safe methods which calls the unsafe method and then passes that output through check_markup using FALLBACK_FORMAT_ID
    //
    $name = strtolower($name);
    if (substr($name, -4) === 'safe') {
      $method = str_replace('safe', '', $name);
      if (!method_exists($this, $method)) {
        throw new \RuntimeException("Method \"$method\" does not exist; therefore method \"$name\" is invalid.");
      }

      $output = call_user_func_array([$this, $method], $arguments);

      // Only scalars may pass through to check_markup.
      return is_scalar($output) ? $this->d7->check_markup($output, $this->core->getSafeMarkupFormat()) : $output;
    }
  }
}
