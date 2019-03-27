<?php

namespace Drupal\loft_core\Entity;

/**
 * Adds functionality if an object needs to work with an account entity.
 *
 * @package Drupal\loft_core\Trait
 */
trait HasUserTrait {

  private $account;

  /**
   * @return object
   */
  public function getUser() {
    return $this->account;
  }

  /**
   * Sets the user entity object
   *
   * @param object $account
   *
   * @return $this
   */
  public function setUser($account) {
    $this->account = $account;

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
   * list($entity_type, $entity) = $this->validateUser();
   * @endcode
   *
   * throws RuntimeException
   */
  protected function validateUser() {
    if (!($account = $this->getUser())) {
      throw new \RuntimeException("Missing user object.");
    }

    return ['user', $account];
  }

}
