<?php

namespace Drupal\loft_core\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\field\Entity\FieldStorageConfig;

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
trait EntityDataAccessorTrait {

  use HasEntityTrait;

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
  public function uri(): string {
    try {
      return $this->getEntity()->toUrl()->toString();
    }
    catch (\Exception $exception) {
      return '';
    }
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
    try {
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

      return $items[$delta][$column] ?? $default;
    }
    catch (\Exception $exception) {
      return $default;
    }
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
  public function safe($default, string $field_name) {
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
  public function items(string $field_name, array $default = []) {
    $entity = $this->getEntity();
    $items = $default;
    if (isset($entity->{$field_name})) {
      $items = [];
      foreach ($entity->get($field_name) as $item) {
        $items[] = $item->getValue();
      }
    }

    return $items;
  }

  /**
   * Return the first loaded entity.
   *
   * @param string $field_name
   *   This should field that references entities.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The first loaded entity referenced by $field_name.
   *
   * @see ::entities()
   */
  public function entity(string $field_name): EntityInterface {
    $entities = $this->entities($field_name);

    return reset($entities);
  }

  /**
   * Return an array of the loaded entities referenced by $field_name.
   *
   * @param string $field_name
   *   The entity reference field_name.
   *
   * @return array
   *   An array of entities instances.  Keys are irrelevant.
   */
  public function entities(string $field_name): array {
    $entities = [];
    try {
      list($entity_type_id, , $bundle) = $this->validateEntity();
      foreach ($this->getEntity()->get($field_name) as $item) {
        $entities[] = $item->getValue();
      }
      if (!empty($entities)) {
        $fields = \Drupal::service('entity_field.manager')
          ->getFieldDefinitions($entity_type_id, $bundle);
        if (!array_key_exists($field_name, $fields)) {
          return $entities;
        }
        $field_definition = $fields[$field_name]->getItemDefinition();
        $target_type = $field_definition->getSetting('target_type');
        $storage = \Drupal::entityTypeManager()->getStorage($target_type);
        $entities = array_map(function ($item) use ($storage, $target_type) {
          return $storage->load($item['target_id']);
        }, $entities);
      }
    }
    catch (\Exception $exception) {
      $entities = [];
    }

    return $entities;
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
   *
   * @throws \RuntimeException
   *   If the entity cannot validate.
   *
   * @see loft_core_shadow_entity_load()
   */
  protected function ensureEntityIsLoaded(): array {
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
      try {
        FieldStorageConfig::loadByName($this->getEntityTypeId(), $field_name);
        $is_field = TRUE;
      }
      catch (\Exception $exception) {
        $is_field = FALSE;
      }
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
