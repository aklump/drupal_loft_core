<?php

namespace Drupal\loft_core\Entity;

trait HasEntityTrait {

  protected $entityTypeId;

  protected $entity;

  /**
   * @return mixed
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Sets the entity type and object
   *
   * @param string $entityTypeId
   * @param Entity|int|object $entity
   *
   * @return $this
   */
  public function setEntity($entityTypeId, $entity) {
    if (is_numeric($entity)) {
      $entities = entity_load($entityTypeId, [$entity]);
      $entity = reset($entities);
    }

    if (!is_null($entity) && !is_object($entity)) {
      throw new \InvalidArgumentException("entity must be an object");
    }

    $this->setEntityTypeId($entityTypeId);

    // Prevent a wrapper inside of a wrapper.
    $this->entity = $entity instanceof Entity ? $entity->getEntity() : $entity;

    return $this;
  }

  /**
   * @return mixed
   */
  public function getEntityTypeId() {
    return $this->entityTypeId;
  }

  /**
   * @param mixed $entityTypeId
   *
   * @return $this
   *
   * I don't think we should expose this, force them to go through
   * setEntity(), so there is no chance to mismatch things.
   * 2016-10-01T07:29, aklump
   */
  protected function setEntityTypeId($entityTypeId) {
    if (method_exists($this, 'getDataApiData')) {
      $this->e = $this->getDataApiData($entityTypeId);
    }
    $this->entityTypeId = $entityTypeId;

    return $this;
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
    if (!($entity = $this->getEntity())) {
      throw new \RuntimeException("Missing entity.");
    }
    if (!($entityTypeId = $this->getEntityTypeId())) {
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
