<?php


namespace Drupal\loft_core\Entity;

use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

/**
 * A trait for simplification of setting data on entities.
 *
 * When using this trait your class MUST:
 * - mark your service as non-shared.
 * - use \Drupal\loft_core\Entity\EntityDataSetterTrait.
 * - use \Drupal\loft_core\Entity\HasEntityTrait.
 * - implement \Drupal\loft_core\Entity\HasEntityInterface.
 *
 * @link https://symfony.com/doc/current/service_container/shared.html
 *
 * @package Drupal\loft_core\Entity
 */
trait EntityDataSetterTrait {

  /**
   * Set a date on a date field.
   *
   * Use this method to make sure the date storage value is formatted properly
   * based on the field settings.
   *
   * @param string|\DateTime||\Drupal\Core\Datetime\DrupalDateTime $date_value
   *   A date value represented by a date string, or date object having a
   *   method "format".
   * @param string $field_name
   *   The fieldname to set the date value on.
   *
   * @return \Drupal\loft_core\Entity\EntityDataSetterTrait
   *   Self for chaining.
   *
   * @throws \InvalidArgumentException
   *   If the $date_value cannot be understood.
   * @throws \Drupal\Core\Entity\EntityStorageExceptiona
   *   If the $field_name is invalid.
   */
  public function setDate($date_value, string $field_name) {
    list(, $entity) = $this->requireEntity();
    $index = 'value';
    if (!is_object($date_value)) {
      if ($value = date_create($date_value)) {
        $date_value = $value;
      }
      else {
        throw new \InvalidArgumentException("Cannot understand string date value of \"$date_value\".");
      }
    }
    if (!method_exists($date_value, 'format')) {
      throw new \InvalidArgumentException("\$date_value must be an object having the method \"format\".");
    }
    $datetime_type = $entity
      ->get($field_name)
      ->getFieldDefinition()
      ->getSetting('datetime_type');
    $storage_format = $datetime_type === DateTimeItem::DATETIME_TYPE_DATE ? DateTimeItemInterface::DATE_STORAGE_FORMAT : DateTimeItemInterface::DATETIME_STORAGE_FORMAT;
    $this->getEntity()
      ->set($field_name, [$index => $date_value->format($storage_format)]);

    return $this;
  }

}
