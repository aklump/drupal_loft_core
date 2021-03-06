<?php

namespace Drupal\loft_core\Entity;

/**
 * A trait for obtaining entity data using convenient/safe methods.
 *
 * All methods that return a string, get a magic safe method.  Here's how it
 * works:
 *
 * Given a method called `getSummary`, you automatically get `getSummarySafe`
 * using this trait.  The safe method uses the makeOutputSafe() method.  If you
 * want to indicate special handling or a filter format then you need to set
 * $this->safeMarkupHandler to a string, which is a filter format, or to a
 * function name, or to a callable.  See CoreInterface::getSafeMarkupHandler()
 * for more info.
 *
 * @package Drupal\loft_core\Entity
 */
trait ExtractorTrait {

  /**
   * Holds the fallback safe markup handler.
   *
   * @var string|callable
   */
  protected $safeMarkupHandler = NULL;

  /**
   * {@inheritdoc}
   */
  public function __call($name, $arguments) {
    $original = $name;
    $name = strtolower($name);
    if (substr($name, -4) === 'safe') {

      //
      //
      // Fallback for *Safe()
      //
      $method = str_replace('safe', '', $name);
      if (!method_exists($this, $method)) {
        throw new \RuntimeException("Method \"$method\" does not exist; therefore method \"$name\" is invalid.");
      }

      // If the format is discovered in the raw function, it should be set using
      // this variable: safeMarkupHandler.
      $this->safeMarkupHandler = NULL;
      $output = call_user_func_array([$this, $method], $arguments);
      if (!is_string($output)) {
        throw new \RuntimeException("Invalid method \"$original\"; only methods that return a string get the magic *Safe method");
      }

      return $this->makeOutputSafe($output);
    }
  }

  public function date($field_name, $default = 'now') {
    return $this->e->getDate($this->getEntity(), [
      $field_name,
      0,
      'value',
    ], $default);
  }

  /**
   * Return the path to the entity.
   *
   * @return string
   *   The local path to the entity.
   */
  public function uri() {
    list($entity_type, $entity) = $this->validateEntity();

    return $this->d7->entity_uri($entity_type, $entity)['path'];
  }

  /**
   * Return data from an entity field in the entity's language.
   *
   * @param mixed $default
   *   The default value if non-existant.
   * @param string $field_name
   *   The field name on the entity.
   *
   * @return mixed
   *   The value of the field as described by the request.
   *
   * @throws \InvalidArgumentException
   *   When $key is not a valid column for $field_name.
   *
   * Examples of how to use:
   * @code
   *   $extract->f('lorem', 'field_pull_quote');
   *   $extract->f('lorem', 'field_pull_quote', 'value');
   *   $extract->f('lorem', 'field_pull_quote', 'value', 0);
   *   $extract->f('lorem', 'field_pull_quote', 0, 'value');
   *   $extract->f('lorem', 'field_pull_quote', 1, 'value');
   *   $extract->f('lorem', 'field_pull_quote', 'value', 1);
   *   $extract->f('lorem', 'field_pull_quote', 'target_id');
   *   $extract->f('lorem', 'field_pull_quote', 'target_id', 0);
   *   $extract->f('lorem', 'field_pull_quote', 0, 'target_id');
   *   $extract->f('lorem', 'field_pull_quote', 'target_id', 1);
   *   $extract->f('lorem', 'field_pull_quote', 1, 'target_id');
   * @endcode
   */
  public function f($default, $field_name) {
    list(, $entity) = $this->ensureEntityIsLoaded();
    $args = func_get_args();
    $default = array_shift($args);
    list($is_field, $field_name, $delta, $column) = $this->getFieldArgs($args, __METHOD__);

    if (!$is_field) {
      return isset($entity->{$field_name}) ? $entity->{$field_name} : $default;
    }
    $items = $this->items($field_name);

    if (!isset($items[$delta])) {
      return $default;
    }
    elseif (is_null($column)) {
      return $items[$delta];
    }

    return isset($items[$delta][$column]) ? $items[$delta][$column] : $default;
  }

  /**
   * Return safe-for-output translated data from an entity field.
   *
   * This is more lightweight than field_view_field() and doesn't take into
   * account anything except the format column on the entity item.  Does not
   * hook into the field apis.
   *
   * If 'safe_value' is present as a column on a field item, it will be used.
   *
   * @see f()
   * @see field_view_field()
   */
  public function safe($default, $field_name) {
    $safeArgs = func_get_args();
    $default = array_shift($safeArgs);
    list($is_field, $field_name, $delta, $column) = $this->getFieldArgs($safeArgs, __METHOD__);
    if (!$is_field) {
      $output = $this->f($default, $field_name);

      return $this->makeOutputSafe($output);
    }
    $item = $this->f([], $field_name, $delta);

    return $this->getFieldItemSafeValue($item, $column);
  }

  /**
   * Return the translated items array for $field_name.
   *
   * @param string $field_name
   *   The entity field name.
   * @param array $default
   *   The default value if the field is missing or empty.
   *
   * @return array
   *   An array of field items in the entity language, if defined, or 'und' if
   *   not.
   */
  public function items($field_name, array $default = []) {
    $entity = $this->getEntity();
    if (isset($entity->{$field_name})) {
      $lang = isset($entity->language) ? $entity->language : 'und';
      if (isset($entity->{$field_name}[$lang])) {
        return $entity->{$field_name}[$lang];
      }
      elseif (isset($entity->{$field_name}['und'])) {
        return $entity->{$field_name}['und'];
      }
    }

    return $default;
  }

  /**
   * Return an array of items cast to safe value strings for a given field.
   *
   * @param string $field_name
   *   The entity field name.
   * @param array $default
   *   The default value if the field is missing or empty.
   *
   * @return array
   *   An array of field items in the entity language, cast to their safe value
   *   strings.
   */
  public function itemsSafe($field_name, array $default = []) {
    return array_map(function ($item) {
      return $this->getFieldItemSafeValue($item);
    }, $this->items($field_name, $default));
  }

  /**
   * Return the first loaded entity.
   *
   * @param string $field_name
   *   This should field that references entities.
   *
   * @return mixed
   *
   * @see ::entities().
   */
  public function entity($field_name) {
    $entities = $this->entities($field_name);

    return reset($entities);
  }

  /**
   * Return an array of field-referenced entities.
   *
   * This has been tested with these modules:
   * - node_reference
   * - entityreference
   * - paragraphs.
   *
   * @param string $field_name
   *   This should field that references entities.
   *
   * @return array
   *   An array of entities.
   */
  public function entities($field_name) {
    if (!($items = $this->items($field_name))) {
      return [];
    }

    static $metadata = NULL;
    if (!isset($metadata[$field_name])) {
      $target_entity_type = NULL;
      $field_info = $this->d7->field_info_field($field_name);
      $field_type = $field_info['type'];
      $field_type_info = $this->d7->field_info_field_types($field_type);

      // How do I know the entity type being referenced by the field?
      if (isset($field_info['settings']['target_type'])) {
        $target_entity_type = $field_info['settings']['target_type'];
      }
      elseif (isset($field_type_info['property_type'])) {
        $target_entity_type = $field_type_info['property_type'];
      }

      // How do I know entity id array key for the reference?
      $value_key = 'value';
      if (!empty($field_info['columns'])) {
        $keys = array_keys($field_info['columns']);
        $value_key = reset($keys);
      }

      if (!$target_entity_type
        || !($type_info = $this->d7->entity_get_info($target_entity_type))
        || empty($type_info['controller class'])) {
        $target_entity_type = NULL;
      }
      $metadata[$field_name]['target_type'] = $target_entity_type;
      $metadata[$field_name]['key'] = $value_key;
    }

    // Convert to an array of ids using $value_key.
    $entity_ids = array_map(function ($item) use ($field_name, $metadata) {
      return $item[$metadata[$field_name]['key']];
    }, $items);

    if (!$metadata[$field_name]['target_type']) {
      return [];
    }

    return array_map(function ($item) {
      if (method_exists($item, 'setHostEntity') && !$item->hostEntity()) {
        $item->setHostEntity($this->getEntityTypeId(), $this->getEntity());
      }

      return $item;
    }, entity_load($metadata[$field_name]['target_type'], $entity_ids));
  }

  /**
   * Handle the safe output of a field item array.
   *
   * @param array $item
   * @param string $column
   *
   * @return bool|float|int|string
   */
  protected function getFieldItemSafeValue($item, $column = 'value') {
    if (isset($item['safe_value'])) {

      //
      //
      // Assume 'safe_value' is safe and do not process.
      //
      return $item['safe_value'];
    }

    //
    //
    // Otherwise use the 'format' check for check_markup().
    //
    $this->safeMarkupHandler = !empty($item['format']) ? $item['format'] : NULL;

    $output = isset($item[$column]) ? $item[$column] : '';

    return $this->makeOutputSafe($output);
  }

  /**
   * Process "safe" on a string; this can be extended if needed.
   *
   * @param $output
   *
   * @return bool|float|int|string
   */
  protected function makeOutputSafe($output) {
    if (!is_scalar($output)) {
      throw new \RuntimeException("Non-scalar cannot be made safe.");

      // TODO Should we throw or not?
      return $output;
    }

    $handler = isset($this->safeMarkupHandler) ? $this->safeMarkupHandler : $this->core->getSafeMarkupHandler();
    if (function_exists($handler) || is_callable($handler)) {
      return $handler($output);
    }
    if (function_exists('filter_formats') && !array_key_exists($handler, filter_formats())) {
      throw new \RuntimeException("Cannot understand safe markup handler \"$handler\"");
    }

    return $this->d7->check_markup($output, $handler);
  }

  /**
   * Ensure that $this->entity is not a shadow entity.
   *
   * @return array
   *   - entity_type
   *   - entity
   *   - bundle_type
   *   - entity_id
   * @see loft_core_shadow_entity_load()
   *
   */
  protected function ensureEntityIsLoaded() {
    list($entity_type, $entity, $bundle, $entity_id) = $this->validateEntity();
    if ($entity_id && property_exists($entity, 'loft_core_shadow') && $entity->loft_core_shadow === FALSE) {
      $entities = $this->d7->entity_load($entity_type, [$entity_id]);
      $this->setEntity($entity_type, $entities[$entity_id]);
    }

    return [$entity_type, $entity, $bundle, $entity_id];
  }

  /**
   * Return information about a field.
   *
   * @param $args
   * @param string $method
   *   The method name of the caller. This is only used for error reporting.
   *
   * @return array
   *  - 0 bool Is this a field.
   *  - 1 string The fieldname.
   *  - 2 int The item delta.
   *  - 3 string The name of the value column.
   */
  private function getFieldArgs($args, $method) {
    // Completed: 1000000 runs in 154 seconds with static cache
    // Completed: 1000000 runs in 161 seconds without static cache
    static $runs = [];
    $cid = json_encode(func_get_args());
    if (!isset($runs[$cid])) {
      $num_args = count($args);
      if ($num_args > 3) {
        throw new \InvalidArgumentException("Expecting max four arguments in $method.");
      }
      $field_name = array_shift($args);
      $delta = $column = NULL;
      $info = (array) $this->d7->field_info_field($field_name);
      $is_field = !empty($info);
      if (!$is_field && $num_args > 1) {
        throw new \InvalidArgumentException("Non-field with field name = \"$field_name\" does not have \$delta or \$column values.");
      }
      if ($is_field) {
        if ($num_args === 1) {
          $column = 'value';
          $delta = 0;
        }
        while (count($args)) {
          $arg = array_shift($args);
          if (is_numeric($arg)) {
            $delta = $arg;
          }
          if (is_string($arg)) {
            $column = $arg;
            $delta = $delta ? $delta : 0;
          }
        }
      }

      $runs[$cid] = [$is_field, $field_name, $delta, $column];
    }

    return $runs[$cid];
  }

}
