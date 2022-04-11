<?php

namespace Drupal\loft_core\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItem;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\time_field\Time;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;

class DatesService {

  protected $config;

  protected $localTimeZone;

  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config = $config_factory;

    // TODO DI.
    $name = \Drupal::config('system.date')
      ->get('timezone.default');
    $this->localTimeZone = new \DateTimeZone($name);
  }

  public function getLocalTimeZone(): \DateTimeZone {
    return $this->localTimeZone;
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
   *
   * @return \Drupal\loft_core\Service\DatesService
   *   Self for chaining.
   */
  public function setEntityFieldDate(EntityInterface $entity, string $field_name, DrupalDateTime $date, bool $end_time = FALSE): self {
    $definition = FieldStorageConfig::loadByName($entity->getEntityTypeId(), $field_name);
    $format = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;
    if ($definition->getSetting('datetime_type') === DateTimeItem::DATETIME_TYPE_DATE) {
      $format = DateTimeItemInterface::DATE_STORAGE_FORMAT;
    }
    $date->setTimeZone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    $key = $end_time ? 'end_value' : 'value';
    $value = $entity->get($field_name)->getValue();
    $value[0][$key] = $date->format($format);
    $entity->set($field_name, $value);

    return $this;
  }

  /**
   * Helper to add properly formatted date queries based on field storage definition.
   *
   * Be aware this only supports the value column (start date), and does not
   * query against the end_value column in the case of date range type fields.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   * @param string $field_name
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   * @param $operator
   * @param bool $end_time
   *
   * @return \Drupal\loft_core\Service\DatesService
   *   Self for chaining.
   */
  public function addEntityQueryDateFieldCondition(QueryInterface $query, string $field_name, DrupalDateTime $date, $operator = NULL): self {
    $definition = FieldStorageConfig::loadByName($query->getEntityTypeId(), $field_name);
    $format = DateTimeItemInterface::DATETIME_STORAGE_FORMAT;
    if ($definition->getSetting('datetime_type') === DateTimeItem::DATETIME_TYPE_DATE) {
      $format = DateTimeItemInterface::DATE_STORAGE_FORMAT;
    }
    $date->setTimeZone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
    $value = $date->format($format);
    $query->condition($field_name, $value, $operator);

    return $this;
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
   * Return a date object from a date entity field in the local timezone.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param string $field_name
   * @param bool $end_time
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  public function getLocalDateTimeByEntity(EntityInterface $entity, string $field_name, bool $end_time = FALSE): DrupalDateTime {
    $date = $this->getUtcDateTimeByEntity($entity, $field_name, $end_time);

    return $date->setTimeZone($this->localTimeZone);
  }

  /**
   * Get local now.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The current moment in the local timezone.
   */
  public function getLocalNow(): DrupalDateTime {
    return new DrupalDateTime('now', $this->localTimeZone);
  }

  /**
   * Get UTC Now.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   *   The current moment in UTC.
   */
  public function getUtcNow(): DrupalDateTime {
    return new DrupalDateTime('now', 'UTC');
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
   * Be aware that that date object timezone is ignored, as is it's time.  So
   * just the date portion will be used.  The $timestamp is local time.  The
   * final object will be adjusted for UTC.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   * @param int $timestamp
   *   This is assumed to be in the site's default timezone.
   *
   * @return \DateTime
   */
  public function getUtcDrupalDateTimeByMergingDrupalDateAndTimeFieldValue(DrupalDateTime $date, int $timestamp) {
    $combined = new DrupalDateTime($date->format('Y-m-d'), $this->localTimeZone);
    $time = Time::createFromTimestamp($timestamp);

    return $combined
      ->setTime($time->getHour(), $time->getMinute(), $time->getSecond())
      ->setTimezone(new \DateTimeZone('UTC'));
  }

  public function getLocalDateTimeByUtcString(string $datetime): DrupalDateTime {
    $date = new DrupalDateTime($datetime, 'UTC');

    return $date->setTimeZone($this->localTimeZone);
  }

  public function getLocalDateTimeByLocalString(string $datetime): DrupalDateTime {
    return new DrupalDateTime($datetime, $this->localTimeZone);
  }


}
