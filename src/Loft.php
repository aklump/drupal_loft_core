<?php

namespace Drupal\loft_core;

use AKlump\LoftLib\Code\Cache;

/**
 * A utility class provided by loft_core.
 */
class Loft {

  /**
   * Recursively set all form properties to a value.
   *
   * For example, you can use this to set all form elements to not required.
   *
   * @code
   *   Loft::overrideValuesByKey($form, '#required', FALSE);
   * @endcode
   *
   * @param mixed &$element
   *   The form element to make not required.
   * @param string $key
   *   The name of the property to set, e.g. #required.
   * @param mixed $override_value
   *   The value to set whenever $key is found in $element.
   * @param int $level
   *   Internal use only.
   */
  public static function overrideValuesByKey(&$element, string $key, $override_value, int $level = 0): void {
    if ($level === 0) {
      $cid = Cache::id([__FUNCTION__, $key, $override_value]);
      if (empty($element['#loft_core']['#processed'])
        || !in_array($cid, $element['#loft_core']['#processed'])) {
        $element['#loft_core']['#processed'][] = $cid;
      }
      else {
        return;
      }
    }
    if (!is_array($element) || empty($element)) {
      return;
    }
    foreach ($element as $key => &$value) {
      if ($key === '#required') {
        $value = FALSE;
      }
      if (is_array($value)) {
        self::overrideValuesByKey($value, $key, $override_value, ++$level);
      }
    }
  }

  /**
   * Build a menu render array by name.
   *
   * @param string $menu_name
   *   The menu name.
   *
   * @return array
   *   The menu render array.
   */
  public static function buildMenu($menu_name): array {
    $menu_tree = \Drupal::MenuTree();
    $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
    $tree = $menu_tree->load($menu_name, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkNodeAccess'],
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
    $tree = $menu_tree->transform($tree, $manipulators);

    return $menu_tree->build($tree);
  }

}
