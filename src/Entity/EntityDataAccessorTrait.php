<?php

namespace Drupal\loft_core\Entity;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * A trait for obtaining entity data using convenient/safe methods.
 *
 * When using this trait your class MUST:
 * - use \Drupal\loft_core\Entity\EntityDataAccessorTrait.
 * - use \Drupal\loft_core\Entity\HasEntityTrait.
 * - implement \Drupal\loft_core\Entity\HasEntityInterface.
 * - set $this->entityTypeManager.
 * - set $this->entityFieldManager.
 *
 * @code
 * arguments: ["@entity_type.manager", "@entity_field.manager"]
 *
 * public function __construct(
 *   EntityTypeManagerInterface $entity_type_manager,
 *   EntityFieldManagerInterface $entity_field_manager
 * ) {
 *   $this->entityTypeManager = $entity_type_manager;
 *   $this->entityFieldManager = $entity_field_manager;
 * }
 * @endcode
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

  /**
   * Holds the fallback safe markup handler.
   *
   * @var string|callable
   */
  protected $safeMarkupHandler = NULL;

  /**
   * An entity type manager instance.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * An entity field manager instance.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

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

  /**
   * Get a date object from a field in a given timezone.
   *
   * This has been tested with the following field types:
   * - Date
   * - DateTime
   * - Timestamp.
   *
   * @param string $default
   *   Fallback used when $field_name is empty.  If this is non-empty, it will
   *   be passed to the DrupalDateTime constructor, otherwise it will be
   *   returned directly.  For example you could pass: NULL or 'now'.
   * @param string $field_name
   *   The field name to look for a value to pass to DrupalDateTime
   *   constructor.  To use a date other than the first item, pass another
   *   argument to this method, a third argument which is a numeric index of
   *   which date to use.  This is most common in a date range where you want
   *   to know the to date, which is index 1.
   * @param ... Optional, any of the following, in any order:
   *   - The item value key, e.g. 'value', 'end_value', default to 'value'.
   *   - The item index for fields with multiple dates, defaults to 0.
   *   - A valid string timezone name passed to timezone_open().  Defaults to
   *   UTC.
   *
   * @code
   *    // Create date object from field_date or return NULL.
   *    $obj->date(NULL, 'field_date');
   *
   *    // Create date object from field_date or from current moment.
   *    $obj->date('now', 'field_date');
   *
   *    // Create date object from field_date's end_date or from current
   *   moment.
   *    $obj->date('now', 'field_date', 'end_date');
   *
   *    // Create date object from field_date second date value.
   *    $obj->date(NULL, 'field_date', 1);
   *
   *    // Create date object from field_date or return NULL.  Set timezone to
   *   LA.
   *    $obj->date(NULL, 'field_date', 'Los_Angeles/America');
   *
   *    // Create date object from field_date using second date value or return
   *   NULL.  Set timezone to LA.
   *    $obj->date('now', 'field_date', 1, 'Los_Angeles/America');
   * @endcode
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime|mixed
   *   An instance in the timezone indicated by $set_timezone_to, or if no
   *   value and $default is empty, $default is returned.
   *
   * @d8
   */
  public function date($default, string $field_name) {
    // Detect the arguments from those passed.
    $args = func_get_args();
    $default = array_shift($args);
    $field_name = array_shift($args);
    $delta = 0;
    $column = 'value';
    $set_timezone_to = 'UTC';
    while ($final_arg = array_pop($args)) {
      if (@timezone_open($final_arg)) {
        $set_timezone_to = $final_arg;
      }
      elseif (is_numeric($final_arg)) {
        $delta = $final_arg;
      }
      elseif ($final_arg) {
        $column = $final_arg;
      }
    }
    // End detection.

    $date = $this->f($default, $field_name, $delta, $column);
    if (empty($date)) {
      return $default;
    }
    // The stored field values use this timezone.
    $current_timezone = DateTimeItemInterface::STORAGE_TIMEZONE;

    // Convert timestamp values to dates.
    if (is_scalar($date) && preg_match('/^\d+$/', $date)) {
      // Timezones are always stored UTC.
      $current_timezone = 'UTC';
      $date = "@$date";
    }

    $obj = new DrupalDateTime($date, $current_timezone, [
      'langcode' => 'en',
    ]);

    return $obj->setTimezone(new \DateTimeZone($set_timezone_to));
  }

  /**
   * Return the unaliased path to the entity.
   *
   * For the aliased path use: $this->getEntity()->toUrl()->toString()
   *
   * @return string
   *   The local path to the entity, e.g. /node/3
   */
  public function path(): string {
    return $this->getEntity()
      ->toUrl('canonical', ['path_processing' => FALSE])
      ->toString();
  }

  /**
   * Return data from an entity field in the entity's language.
   *
   * The column can be omitted from the arguments, only when the expected
   * column is value.  If value does not exist as a column, then an exception
   * will be thrown; the default will not be returned.  However, if you pass
   * any column name, including 'value' and that column does not exist, the
   * $default will be returned.
   *
   * You can omit 'value', when you want 'value' so long as the field has a
   * 'value' column.  If you try to omit 'value' and the field doesn't contain
   * a 'value' field, you will receive an exception to protect yourself from
   * bad code.
   *
   * Examples of how to use:
   *
   * @code
   *   // All of these will return field_pull_quote.0.value:
   *   $extract->f('lorem', 'field_pull_quote');
   *   $extract->f('lorem', 'field_pull_quote', 'value');
   *   $extract->f('lorem', 'field_pull_quote', 'value', 0);
   *
   *   // To get field_pull_quote.0, an array:
   *   $extract->f('lorem', 'field_pull_quote', 0);
   *
   *   // Note: To get all items use ::items.
   *
   *   // Other configurations; notice order doesn't matter.
   *   $extract->f('lorem', 'field_pull_quote', 0, 'value');
   *   $extract->f('lorem', 'field_pull_quote', 1, 'value');
   *   $extract->f('lorem', 'field_pull_quote', 'value', 1);
   *
   *   // As mentioned above, you cannot omit 'target_id' because an exception
   *   will be thrown, since field_related does not have a 'value' column.
   *   $extract->f('lorem', 'field_related', 'target_id');
   *   $extract->f('lorem', 'field_related', 'target_id', 0);
   *   $extract->f('lorem', 'field_related', 0, 'target_id');
   *   $extract->f('lorem', 'field_related', 'target_id', 1);
   *   $extract->f('lorem', 'field_related', 1, 'target_id');
   * @endcode
   *
   * @param mixed $default
   *   The default value if non-existant.
   * @param string $field_name
   *   The field name on the entity.
   *
   * @return mixed
   *   The value of the field as described by the request.
   *
   * @throws \Drupal\loft_core\Entity\MissingRequiredEntityException
   */
  public function f($default, $field_name) {
    list(, $entity) = $this->requireEntity();
    $args = func_get_args();
    $default = array_shift($args);

    list($is_field, $field_name, $delta, $column) = $this->getFieldArgs($args, __METHOD__);

    $column_is_assumed = !in_array($column, $args);

    if (!$is_field) {
      if (isset($entity->{$field_name})) {
        if (count($args) > 1) {
          throw new \BadMethodCallException("You may not pass delta or column values for non-field entity value, e.g. \"\$$field_name\".");
        }

        return $entity->{$field_name};
      }

      return $default;
    }
    $items = $this->items($field_name);

    if (isset($items[$delta])) {
      if (is_null($column)) {
        return $items[$delta];
      }
      elseif (isset($items[$delta][$column])) {
        return $items[$delta][$column];
      }
    }

    if (isset($items[$delta]) && $column_is_assumed) {
      throw new \OutOfBoundsException('Missing column argument and column "value" does not exist; a column key assumption will not be made.');

    }

    return $default;
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
    list(, $entity) = $this->requireEntity();
    $items = $default;
    $exists = FALSE;
    try {
      // TODO Can we refactor with entity->getValue?
      foreach ($entity->get($field_name) as $item) {
        if ($exists === FALSE) {
          $items = [];
          $exists = TRUE;
        }
        $items[] = $item->getValue();
      }
    }
    catch (\Exception $exception) {
      $items = $default;
    }

    return $items;
  }

  /**
   * Return the first loaded entity.
   *
   * @param string $field_name
   *   This should field that references entities.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The first loaded entity referenced by $field_name or NULL.
   *
   * @see ::entities()
   */
  public function entity(string $field_name) {
    $entities = $this->entities($field_name);
    $entity = reset($entities);

    return $entity ? $entity : NULL;
  }

  /**
   * Return an array of the loaded entities referenced by $field_name.
   *
   * @param string $field_name
   *   The entity reference field_name.
   *
   * @return array
   *   An array of entities instances.  Keys are entity ids.
   */
  public function entities(string $field_name): array {
    list($entity_type_id, , $bundle) = $this->requireEntity();
    $entities = [];
    try {
      foreach ($this->getEntity()->get($field_name) as $item) {
        $entities[] = $item->getValue();
      }
      if (!empty($entities)) {
        $fields = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
        $field_definition = $fields[$field_name]->getItemDefinition();
        $target_type = $field_definition->getSetting('target_type');
        $storage = $this->entityTypeManager->getStorage($target_type);
        $entities = $storage->loadMultiple(array_map(function ($item) {
          return $item['target_id'];
        }, $entities));
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
   * Return information about a field.
   *
   * @param array $args
   * @param string $method
   *   The method name of the caller. This is only used for error reporting.
   *
   * @return array
   *  - 0 bool Is this a field.
   *  - 1 string The fieldname.
   *  - 2 int The item delta.
   *  - 3 string The name of the value column.
   */
  private function getFieldArgs(array $args, $method) {
    list(, $entity) = $this->requireEntity();
    $num_args = count($args);
    if ($num_args > 3) {
      throw new \BadMethodCallException("Expecting max four arguments in $method.");
    }
    $field_name = array_shift($args);
    $delta = $column = NULL;
    try {
      $is_field = (bool) $entity->hasField($field_name);
    }
    catch (\Exception $exception) {
      $is_field = FALSE;
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

    $info = [$is_field, $field_name, $delta, $column];

    return $info;
  }

}
