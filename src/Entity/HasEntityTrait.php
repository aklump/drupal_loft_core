<?php

namespace Drupal\loft_core\Entity;

use Drupal\Core\Entity\EntityInterface;

/**
 * Trait HasNodeTrait for classes handling a single entity object.
 *
 * @package Drupal\loft_core\Entity
 */
trait HasEntityTrait {

  /**
   * Holds the object instance.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   *
   * phpcs:disable Drupal.NamingConventions.ValidVariableName.LowerCamelName
   */
  private $_entity;

  /**
   * Get the entity instance.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity instance.
   */
  public function getEntity(): EntityInterface {
    return $this->_entity;
  }

  /**
   * Sets the entity type and object.
   *
   * @param string $entityTypeId
   *   The entity type id.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity instance.
   *
   * @return $this
   */
  public function setEntity(string $entityTypeId, EntityInterface $entity) {
    if (is_numeric($entity)) {
      $entities = entity_load($entityTypeId, [$entity]);
      $entity = reset($entities);
    }
    if (!$entity instanceof EntityInterface) {
      throw new \InvalidArgumentException("entity must be an object");
    }
    $this->_entity = $entity;

    return $this;
  }

  /**
   * Return the entity type id.
   *
   * @return string
   *   The entity type id.
   */
  public function getEntityTypeId(): string {
    return $this->_entity->getEntityTypeId();
  }

  /**
   * Validate (or throw) and return type and entity.
   *
   * This should be used by public methods that will use the entity.
   *
   * @return array
   *   - entity_type
   *   - entity
   *
   * @code
   * list($entity_type, $entity) = $this->validateEntity();
   * @endcode
   *
   * throws RuntimeException
   */
  public function validateEntity($validateBundleType = NULL) {
    try {
      $entity = $this->getEntity();
    }
    catch (\Error $error) {
      throw new \RuntimeException("Missing entity.");
    }
    if (!($entityTypeId = $entity->getEntityTypeId())) {
      throw new \RuntimeException("Missing entity type.");
    }
    $entityId = $entity->id();
    $bundleType = $entity->getType();
    if ($validateBundleType && $bundleType !== $validateBundleType) {
      throw new \RuntimeException("Entity type must be $validateBundleType");
    }

    return [$entityTypeId, $entity, $bundleType, $entityId];
  }

}
