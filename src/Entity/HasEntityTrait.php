<?php

namespace Drupal\loft_core\Entity;

use AKlump\LoftLib\Code\Cache;
use Drupal\Core\Entity\EntityInterface;

/**
 * Trait HasEntityTrait.
 *
 * Add to classes that will work with one entity of a type at a time.  This
 * trait supports working with multiple entities simultaneously as long as they
 * are not of the same entity type id (e.g. one node and one user).
 *
 * When using this trait your class MUST:
 * - implement \Drupal\loft_core\Entity\HasEntityInterface.
 * - define a class constant ENTITY_TYPE_ID to use as default.
 * Your class SHOULD:
 * - make use of ::requireEntity() inside of other methods.
 * Your class MAY:
 * - implement ::onGetEntityCacheId(string $entity_type_id): array to provide
 * extra cache information that affects the cache id.
 * - implement ::onSetEntity($entity), to alter the $entity before it's set, or
 * otherwise do something when and $entity is being set.  To prevent the entity
 * from being set, you should throw an exception.
 *
 * @code
 * protected function onSetEntity($entity) {
 *   $final = $entity;
 *   if ($story = $this->entity('field_cover_story')) {
 *
 *     // Use the story entity instead.
 *     $final = $story;
 *   }
 *
 *   // Pass the entity to another class service needing it.
 *   $this->extract->setEntity($final);
 *
 *   return $final;
 * }
 *
 * private function onGetEntityCacheId(): array {
 *   return [
 *     $this->nodeTypes,
 *     $this->cacheGroup,
 *   ];
 * }
 * @endcode
 *
 * @see \Drupal\loft_core\Entity\HasNodeTrait
 * @see \Drupal\loft_core\Entity\HasUserTrait
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
  private $_entities = [];

  /**
   * Sets the entity for a given entity type id.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity instance.  If another entity of this same entity type id is
   *   already set, this method will replace that with $entity.
   *
   * @return $this
   *   Self for chaining.
   *
   * @see ::onSetEntity()
   */
  public function setEntity(EntityInterface $entity) {
    $value = $entity;
    if (method_exists($this, 'onSetEntity') && ($return = $this->onSetEntity($entity))) {
      if (!$return instanceof EntityInterface) {
        throw new \UnexpectedValueException("onSetEntity must return an instance of " . EntityInterface::class);
      }
      $value = $return;
    }
    $this->_entities[$entity->getEntityTypeId()] = $value;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasEntity(string $entity_type_id = ''): bool {
    $this->ensureEntityTypeId($entity_type_id);

    return isset($this->_entities[$entity_type_id]);
  }

  /**
   * Get the entity instance by entity type id.
   *
   * @param string $entity_type_id
   *   The entity type you want to retrieve.  Each class can have one entity of
   *   each type: one node, one user, etc.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity instance.
   */
  public function getEntity(string $entity_type_id = ''): EntityInterface {
    $this->ensureEntityTypeId($entity_type_id);
    $this->requireEntity($entity_type_id);

    return $this->_entities[$entity_type_id];
  }

  /**
   * Return a string to be used as a unique cache key for an entity.
   *
   * @param string $entity_type_id
   *   The entity type.
   *
   * @return string
   *   A string that is unique representation of this entity across the
   *   application system.  This does not need to be a UUID, but it must be
   *   unique within the system that this Trait is being used.
   *
   *   To add additional configuration you can implement @code
   *   ::onGetEntityCacheId(string $entity_type_id): array @endcode, which
   *   returns an associate array of key/values, if any of the values change
   *   the cache id will change too.  The order of the keys does not matter,
   *   however.
   *
   * @throws \DomainException
   *   When the entity_type_id is indeterminate.
   * @throws \Drupal\loft_core\Entity\MissingRequiredEntityException
   */
  public function getEntityCacheId(string $entity_type_id = ''): string {
    $this->ensureEntityTypeId($entity_type_id);
    list($entity_type_id) = $this->requireEntity($entity_type_id);
    $config = [];
    if (method_exists($this, 'onGetEntityCacheId')) {
      $config = $this->onGetEntityCacheId($entity_type_id);
    }
    $config += [
      'type' => $entity_type_id,
      'id' => $this->getEntity($entity_type_id)->id(),
    ];

    return Cache::id($config);
  }

  /**
   * Require that an entity of a certain type be set or throw.
   *
   * Use this inside of methods of the class to ensure that they can be run
   * properly, when they require a certain entity instance. Optionally, you may
   * specify one or more bundles that the entity must be.  Not all entities
   * have bundles.
   *
   * @code
   *  public function someClassMethod() {
   *    list($entity_type_id, $entity, $bundle, $id) =
   *   $this->requireEntity('node');
   *    ...
   * @endcode
   *
   * @param string $entity_type_id
   *   The required entity_type_id.  Pass an empty value to use
   *   static::ENTITY_TYPE_ID.
   * @param array $required_bundles
   *   To allow all bundles leave these an empty array.  To require one or more
   *   bundles, send those bundle ids as an indexed array.
   *
   * @return array
   *   The entity type id, entity, bundle and it's id as indexed array.
   *
   * @throws \Drupal\loft_core\Entity\MissingRequiredEntityException
   *   If the entity of said type is not set, or it it's not one of the
   *   required bundles.
   * @throws \InvalidArgumentException
   *   If $entity_type_id is empty.
   */
  protected function requireEntity(string $entity_type_id = '', array $required_bundles = []) {
    $this->ensureEntityTypeId($entity_type_id);
    if (!$this->hasEntity($entity_type_id)
      || !($entity = $this->_entities[$entity_type_id])
      || (count($required_bundles) && !in_array($entity->bundle(), $required_bundles))
    ) {
      throw new MissingRequiredEntityException($entity_type_id, $required_bundles);
    }

    return [$entity_type_id, $entity, $entity->bundle(), $entity->id()];
  }

  /**
   * Set the default entity type id on $provided.
   *
   * This will be used if the entity type id is needed, but not provided.
   *
   * @param string &$provided
   *   The provided entity_type_id.  If empty, we'll look for the defined class
   *   constant and set it's value.
   *
   * @throws \Drupal\loft_core\Entity\IncompleteImplementationException
   *   When the default is indeterminate.
   */
  private function ensureEntityTypeId(string &$provided): void {
    if (!$provided) {
      $constant_name = static::class . '::ENTITY_TYPE_ID';
      if (!defined($constant_name)) {
        throw new IncompleteImplementationException("Missing class constant ENTITY_TYPE_ID in " . get_class($this));
      }

      $provided = static::ENTITY_TYPE_ID;
    }
  }

}
