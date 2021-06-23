<?php

namespace Drupal\loft_core\WrappedEntities;

/**
 * Add this trait to allow the wrapped entity's traits to be called on the wrapper.
 *
 * This should not be abused because it's non-documenting.  Therefore by default
 * the methods are limited to a handful.  You can control the whitelist by
 * setting an array of allowed methods in a class variable called
 * `bubbleUpMethods`.
 *
 * For example this allows you to call ::toLink on the wrapped entity.
 *
 * @code
 *   $wrapped_entity =  typed_entity_repository_manager()->wrap($node);
 *   $link = $wrapped_entity->toLink();
 * @endcode
 *
 * Trait BubbleUpEntityMethodsTrait
 */
trait BubbleUpEntityMethodsTrait {

  /**
   * Allow the entity methods to be called on the wrapper.
   *
   * @param $name
   * @param $arguments
   *
   * @return false|mixed
   */
  public function __call($name, $arguments) {
    $bubble_up_methods = $this->bubbleUpMethods ?? [
        'bundle',
        'getTitle',
        'getType',
        'getOwnerId',
        'id',
        'isPromoted',
        'isSticky',
        'toLink',
        'toUrl',
      ];
    if (in_array($name, $bubble_up_methods)
      && method_exists($this->getEntity(), $name)) {
      return call_user_func_array([$this->getEntity(), $name], $arguments);
    }
    throw new \RuntimeException(sprintf('Call to undefined method %s::%s', get_called_class(), $name));
  }

}
