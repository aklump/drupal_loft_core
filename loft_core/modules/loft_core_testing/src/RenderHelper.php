<?php

namespace Drupal\loft_core_testing;

use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\Core\Template\Attribute;

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
        if ($element['#attributes'] instanceof Attribute) {
          $element['#attributes']->addClass($class);
        }
        elseif (is_array($element['#attributes'])) {
          $element['#attributes']['class'][] = $class;
        }
      }
    }

    return $element;
  }
}
