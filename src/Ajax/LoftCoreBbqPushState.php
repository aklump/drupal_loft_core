<?php

namespace Drupal\loft_core\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * A command to update the URL has via AJAx.
 */
class LoftCoreBbqPushState implements CommandInterface {

  /**
   * LoftCoreBbqPushState constructor.
   *
   * @param string $hash_string
   *   The fragment to push into the URL, e.g., '#sm=registration.start'.  It may
   *   or may not begin with '#'.
   */
  public function __construct(string $hash_string) {
    $this->hashString = $hash_string;
  }

  public function render() {
    return [
      'command' => 'loftCoreAjaxBbqPushState',
      'data' => [
        'hash' => '#' . ltrim($this->hashString, '#'),
      ],
    ];
  }
}
