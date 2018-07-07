<?php

namespace Drupal\loft_core\Entity;

/**
 * Trait ExtractorTrait
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

  protected $safeMarkupHandler = NULL;

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

      // If the format is discovered in the raw function, it should be set using this variable: safeMarkupHandler
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
   */
  public function uri() {
    list($entity_type, $entity) = $this->validateEntity();

    return $this->d7->entity_uri($entity_type, $entity)['path'];
  }

  /**
   * Return data from an entity field in the entity's language.
   *
   * @param string $field_name The field name on the entity.
   * @param mixed $default The default value if non-existant.
   * @param int|null $item The item index for a field entity, e.g. 0, 1, 2.
   *   Send null to load the entire array for the given language.
   * @param string|null $key The key of a single item array.  This is ignored
   *   when $item is null.
   *
   * @return mixed
   *
   * @throws \InvalidArgumentException when $key is not a valid column for
   *   $field_name.
   *
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
  public function f($default = NULL, $field_name) {
    $this->ensureEntityIsLoaded();
    $args = func_get_args();
    $default = array_shift($args);
    list(, $field_name, $delta, $column) = $this->getFieldArgs($args, __METHOD__);
    $value = $this->e->get($this->getEntity(), null_filter([
      $field_name,
      $delta,
      $column,
    ]), $default);

    return $value;
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
   * @param array $default
   *
   * @return array
   */
  public function items($field_name, array $default = []) {
    return $this->e->get($this->getEntity(), $field_name, $default);
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

    return $metadata[$field_name]['target_type'] ? entity_load($metadata[$field_name]['target_type'], $entity_ids) : [];
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
    if (!array_key_exists($handler, filter_formats())) {
      throw new \RuntimeException("Cannot understand safe markup handler \"$handler\"");
    }

    return $this->d7->check_markup($output, $handler);
  }

  /**
   * Ensure that $this->entity is not a shadow entity.
   *
   * @see loft_core_shadow_entity_load()
   */
  protected function ensureEntityIsLoaded() {
    list($entity_type, $entity, , $entity_id) = $this->validateEntity();
    if ($entity_id && property_exists($entity, 'loft_core_shadow') && $entity->loft_core_shadow === FALSE) {
      $entities = entity_load($entity_type, [$entity_id]);
      $this->setEntity($entity_type, $entities[$entity_id]);
    }
  }

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
