<?php

namespace Drupal\loft_core\Service;

interface AudioMediaInterface {

  /**
   * @param string $uri
   *
   * @return int
   *   The duration in seconds of an audio $uri.
   */
  public function getDuration(string $uri): int;

}
