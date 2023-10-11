<?php

namespace Drupal\loft_core\Service;

use Owenoj\LaravelGetId3\GetId3;

class AudioService implements AudioMediaInterface {

  /**
   * @inheritDoc
   */
  public function getDuration(string $uri): int {
    $audio = new GetId3($uri);

    return intval($audio->getPlaytimeSeconds());
  }
}
