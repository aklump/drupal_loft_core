<?php

namespace Drupal\loft_core;

use AKlump\LoftLib\Code\Cache;
use Drupal\Core\Entity\EntityInterface;
use Drupal\loft_core\Service\DatesService;
use Drupal\loft_core\Service\ImageService;

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
   *   The menu name.  To find the menu name through th UI, edit a menu and
   *   pull the last element from the URL, e.g., 'desktop-header-dropdown'.  To
   *   see all menu names using SQL try: SELECT DISTINCT(menu_name) FROM
   *   menu_tree;.
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


  /**
   * Return an array of entities that point to a known entity.
   *
   * Given a known entity, $target_entity, and a reference field name along
   * with the type of content you want to find that references the known
   * entity, with optional bundle filter, you may use this method to find those
   * entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface $target_entity
   *   The known entity, which you want to know: What other entities reference
   *   this one?
   * @param array $reference_field_names
   * @param string $referencing_entity_type
   *   The type of content that you wish to find, which references
   *   $target_entity.
   * @param array $referencing_bundle_types
   *   Optionally, limit the bundles of the content that will be returned, the
   *   bundles that reference $target_entity.
   * @param bool $load_entities
   *   Defaults to true.  Set this to false and an array of ids will be
   *   returned instead of loading the entities.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]|int[]
   *   An array of reverse reference ids or entities.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function entityReverseReference(
    EntityInterface $target_entity,
    array $reference_field_names,
    string $referencing_entity_type,
    array $referencing_bundle_types = [],
    bool $load_entities = TRUE
  ): array {
    $query = \Drupal::entityQuery($referencing_entity_type);
    $field_names = $query->orConditionGroup();
    foreach ($reference_field_names as $reference_field_name) {
      $field_names->condition($reference_field_name, $target_entity->id());
    }
    $query->condition($field_names);
    if ($referencing_bundle_types) {
      $query->condition('type', $referencing_bundle_types, 'IN');
    }
    $found = $query->execute();
    if (!$load_entities) {
      return array_map('intval', array_values($found));
    }

    $references = \Drupal::entityTypeManager()
      ->getStorage($referencing_entity_type)
      ->loadMultiple($found);

    return array_values($references);
  }

  /**
   * Service wrapper.
   *
   * @return \Drupal\loft_core\Service\ImageService
   *   An instance of the service instance
   */
  public static function images(): ImageService {
    return \Drupal::service('loft_core.images');
  }

  /**
   * @return \Drupal\loft_core\Service\DatesService
   */
  public static function dates(): DatesService {
    return \Drupal::service('loft_core.dates');
  }

}
