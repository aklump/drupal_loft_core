<?php

namespace Drupal\loft_core_testing;

use Drupal\Core\Security\TrustedCallbackInterface;

final class RenderHelper implements TrustedCallbackInterface {

  /**
   * @inheritDoc
   */
  public static function trustedCallbacks() {
    return ['addTestingClasses'];
  }


  public static function addTestingClasses($element) {
    if (isset($element['#loft_core_testing'])) {
      foreach ($element['#loft_core_testing'] as $class) {
        $element['#attributes']['class'][] = $class;
      }
    }

    return $element;
  }
}
