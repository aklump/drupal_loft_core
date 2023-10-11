<?php

namespace Drupal\loft_core\Entity;

use Drupal\Core\Entity\EntityInterface;

/**
 * Interface HasEntityInterface.
 *
 * For classes that rely on an entity object.
 *
 * @package Drupal\loft_core\Entity
 */
interface HasEntityInterface {

  /**
   * Set the entity object.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity instance.
   *
   * @return \Drupal\loft_core\Entity\HasEntityInterface
   *   Self for chaining.
   */
  public function setEntity(EntityInterface $entity);

  /**
   * Check if an entity of type id has been set.
   *
   * @param string $entity_type_id
   *   The entity type id.  Each class can have one entity of
   *   each type: one node, one user, etc.  If you want to make $entity_type_id
   *   optional, you must set a const on your class ENTITY_TYPE_ID with the
   *   default type to use when not set.  If this constant is not set and you
   *   try to call this without an argument, an exception will be thrown.
   *
   * @return bool
   *   True if entity has been set.
   */
  public function hasEntity(string $entity_type_id = ''): bool;

  /**
   * Get the entity instance by entity type id.
   *
   * @param string $entity_type_id
   *   The entity type id.  Each class can have one entity of
   *   each type: one node, one user, etc.  If you want to make $entity_type_id
   *   optional, you must set a const on your class ENTITY_TYPE_ID with the
   *   default type to use when not set.  If this constant is not set and you
   *   try to call this without an argument, an exception will be thrown.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity instance.
   */
  public function getEntity(string $entity_type_id = ''): EntityInterface;

  /**
   * Return a string to be used as a unique cache key for an entity.
   *
   * @param string $entity_type_id
   *   The entity type id.  Each class can have one entity of
   *   each type: one node, one user, etc.  If you want to make $entity_type_id
   *   optional, you must set a const on your class ENTITY_TYPE_ID with the
   *   default type to use when not set.  If this constant is not set and you
   *   try to call this without an argument, an exception will be thrown.
   *
   * @return string
   *   A string that is unique representation of this entity across the
   *   application system.  This does not need to be a UUID, but it must be
   *   unique within the system that this Trait is being used.
   */
  public function getEntityCacheId(string $entity_type_id = ''): string;

}
