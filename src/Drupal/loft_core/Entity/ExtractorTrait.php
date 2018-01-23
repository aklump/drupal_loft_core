<?php

namespace Drupal\loft_core\Entity;

trait ExtractorTrait {

  protected $safeMarkupFormat = NULL;

  public function __call($name, $arguments) {
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

      // If the format is discovered in the raw function, it should be set using this variable.
      $this->safeMarkupFormat = NULL;

      $output = call_user_func_array([$this, $method], $arguments);

      return $this->makeOutputSafe($output);
    }
  }

  public function date($field_name, $default = 'now') {
    return $this->e->getDate($this->getEntity(), [$field_name, 0, 'value'], $default);
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
   * @param string      $field_name The field name on the entity.
   * @param mixed       $default    The default value if non-existant.
   * @param int|null    $item       The item index for a field entity, e.g. 0, 1, 2.  Send null to load the entire
   *                                array for the given language.
   * @param string|null $key        The key of a single item array.  This is ignored when $item is null.
   *
   * @return mixed
   *
   * @throws \InvalidArgumentException when $key is not a valid column for $field_name.
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
    $value = $this->e->get($this->getEntity(), null_filter([$field_name, $delta, $column]), $default);

    return $value;
  }

  /**
   * Return safe-for-output translated data from an entity field.
   *
   * This is more lightweight than field_view_field() and doesn't take into account anything except the format column
   * on the entity item.  Does not hook into the field apis.
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
    }
    else {
      $item = $this->f([], $field_name, $delta);
      if (isset($item['safe_value'])) {

        //
        //
        // Assume 'safe_value' is safe and do not process.
        //
        return $item['safe_value'];
      }
      $output = isset($item[$column]) ? $item[$column] : '';

      //
      //
      // Otherwise use the 'format' check for check_markup().
      //
      $this->safeMarkupFormat = !empty($item['format']) ? $item['format'] : NULL;;
    }

    return $this->makeOutputSafe($output);
  }

  /**
   * Return the translated items array for $field_name.
   *
   * @param string $field_name
   * @param array  $default
   *
   * @return array
   */
  public function items($field_name, array $default = array()) {
    return $this->e->get($this->getEntity(), $field_name, $default);
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

    $handler = isset($this->safeMarkupFormat) ? $this->safeMarkupFormat : $this->core->getSafeMarkupHandler();
    if (function_exists($handler) || is_callable($handler)) {
      return $handler($output);
    }
    if (!in_array($handler, filter_formats())) {
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
