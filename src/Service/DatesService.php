<?php

namespace Drupal\loft_core\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\time_field\Time;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

class DatesService {

  protected $config;

  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory;
  }

  /**
   * Set the correctly formatted date value on an entity field.
   *
   * You must call ::save to persist the data.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param string $field_name
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   * @param bool $end_time
   */
  public function setEntityFieldDate(EntityInterface $entity, string $field_name, DrupalDateTime $date, bool $end_time = FALSE) {
    $definition = FieldStorageConfig::loadByName($entity->getEntityTypeId(), $field_name);
    $format = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;
    if ($definition->getSetting('datetime_type') === DateTimeItem::DATETIME_TYPE_DATE) {
      $format = DateTimeItemInterface::DATE_STORAGE_FORMAT;
    }
    $date->setTimeZone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    $key = $end_time ? 'end_value' : 'value';
    $entity->set($field_name, [
      $key => $date->format($format),
    ]);
  }

  /**
   * Return a date object from a date entity field normalized to UTC.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param string $field_name
   * @param bool $end_time
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  public function getUtcDateTimeByEntity(EntityInterface $entity, string $field_name, bool $end_time = FALSE): DrupalDateTime {
    $value = $end_time ? 'end_value' : 'value';
    $date_value = $entity->$field_name->$value;
    $date = new DrupalDateTime($date_value, DateTimeItemInterface::STORAGE_TIMEZONE);

    return $date->setTimeZone(new \DateTimeZone('UTC'));
  }

  /**
   * Returns date object with time set to 00:00:00 or 23:59:59.
   *
   * The date portion is pulled from the field entity, but the time is ignored.
   * If $end_time is true then the time value is set to 23:59:59.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param string $field_name
   * @param bool $end_time
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  public function getUtcDrupalDateTimeWithExtremeTimeByEntity(EntityInterface $entity, string $field_name, bool $end_time = FALSE): DrupalDateTime {
    $date = $this->getUtcDateTimeByEntity($entity, $field_name, $end_time);
    if ($end_time) {
      return $date->setTime(23, 59, 59);
    }

    return $date->setTime(0, 0);
  }

  /**
   * Merges a date object with a Time field timestamp.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   * @param int $timestamp
   *   This is assumed to be in the site's default timezone.
   *
   * @return \DateTime
   */
  public function getUtcDrupalDateTimeByMergingDrupalDateTimeAndTimeFieldValue(DrupalDateTime $date, int $timestamp) {
    return Time::createFromTimestamp($timestamp)
      ->on($date->getPhpDateTime())
      ->setTimezone(new \DateTimeZone('UTC'));
  }

}
