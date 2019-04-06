<?php

namespace Drupal\loft_core\Entity;

use Drupal\user\UserInterface;

/**
 * Adds functionality if an object needs to work with an account entity.
 *
 * When using this trait your class MUST:
 * - use \Drupal\loft_core\Entity\HasEntityTrait
 * - implement \Drupal\loft_core\Entity\HasUserInterface.
 * Your class SHOULD:
 * - make use of ::requireUser() inside of other methods.
 *
 * @package Drupal\loft_core\Trait
 */
trait HasUserTrait {

  /**
   * {@inheritdoc}
   */
  public function setUser(UserInterface $user): self {
    return $this->setEntity($user);
  }

  /**
   * {@inheritdoc}
   */
  public function hasUser(): bool {
    return $this->hasEntity('user');
  }

  /**
   * {@inheritdoc}
   */
  public function getUser(): UserInterface {
    return $this->getEntity('user');
  }

  /**
   * Require that a user be set or throw.
   *
   * Use this inside of methods on this class to ensure that they can be run
   * properly, when they require a user instance.
   *
   * @code
   *  public function someClassMethod() {
   *    list($user, $uid) = $this->requireUser();
   *    ...
   * @endcode
   *
   * @return array
   *   The user entity and id as indexed array.
   *
   * @throws \Drupal\loft_core\Entity\MissingRequiredEntityException
   *   If the user is not set.
   */
  protected function requireUser() {
    $list = $this->requireEntity('user');

    return [$list[1], $list[3]];
  }

}
