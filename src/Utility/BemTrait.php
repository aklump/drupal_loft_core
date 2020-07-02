<?php

namespace Drupal\loft_core\Utility;

/**
 * Trait BemTrait handles block/element/modifier CSS.
 *
 * To use this trait your class must implement the method ::bemBlock, which
 * returns the base value for BEM concantenation.
 *
 * @code
 *   public function bemBlock(): string {
 *     return 'user-collections';
 *   }
 * @endcode
 *
 * @deprecated Use \Drupal\front_end_components\BemTrait instead and declare a
 *   dependency on that module.
 */
trait BemTrait {

  /**
   * Return the BEM block string.
   *
   * @return string
   *   The element string.
   */
  abstract public function bemBlock(): string;

  /**
   * Return a BEM element string.
   *
   * @param string $suffix
   *   The suffix to append to the block.
   *
   * @return string
   *   The element string.
   */
  public function bemElement(string $suffix): string {
    list($callback) = loft_core_bem($this->bemBlock());

    return $callback($suffix);
  }

  /**
   * Return a BEM modifier string.
   *
   * @param string $suffix
   *   The suffix to append to the block.
   *
   * @return string
   *   The modifier string.
   */
  public function bemModifier(string $suffix): string {
    list(, $callback) = loft_core_bem($this->bemBlock());

    return $callback($suffix);
  }

}
