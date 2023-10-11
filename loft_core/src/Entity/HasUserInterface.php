<?php

namespace Drupal\loft_core\Entity;

use Drupal\Core\Session\AccountInterface;
use Drupal\user\UserInterface;

/**
 * Interface HasUserInterface
 *
 * Add to classes that will be working with a single user entity.
 *
 * @package Drupal\loft_core\Entity
 */
interface HasUserInterface extends HasEntityInterface {

  /**
   * Get the user.
   *
   * @return \Drupal\user\UserInterface
   *   The user object.
   */
  public function getUser(): UserInterface;

  /**
   * Check if a user is set.
   *
   * @return bool
   *   True if a user is set.
   */
  public function hasUser(): bool;

  /**
   * Set the user object.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user instance.
   *
   * @return \Drupal\loft_core\Entity\HasUserTrait
   *   Self for chaining.
   */
  public function setUser(AccountInterface $user);

}
