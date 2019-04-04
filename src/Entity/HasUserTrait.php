<?php

namespace Drupal\loft_core\Entity;

use Drupal\Core\Session\AccountInterface;

/**
 * Adds functionality if an object needs to work with an account entity.
 *
 * @package Drupal\loft_core\Trait
 */
trait HasUserTrait {

  private $account;

  /**
   * Get the user.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   The user object.
   */
  public function getUser(): AccountInterface {
    return $this->account;
  }

  /**
   * Set the user object.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user instance.
   *
   * @return \Drupal\loft_core\Entity\HasUserTrait
   *   Self for chaining.
   */
  public function setUser(AccountInterface $account): HasUserTrait {
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
