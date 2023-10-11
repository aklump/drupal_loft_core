<?php

namespace Drupal\loft_core\Entity;

use Drupal\Core\Routing\Access\AccessResultInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Assist with entity CRUD.
 *
 * In your custom module you need to:
 *
 * - Extend this class, e.g. UserCrudService extends EntityCrudService
 * - Add it as service in your modules services.yml.
 * - Wrap a service call in the Drupal hooks, see code below.
 * - Implement a method like save or save__BUNDLE.
 *
 * @code
 *  function my_module_entity_presave(EntityInterface $entity) {
 *    \Drupal::service('my_module.user_crud')->setUser($entity)->hook('presave');
 *    \Drupal::service('my_module.node_crud')->setNode($entity)->hook('presave');
 *  }
 * @endcode
 */
abstract class EntityCrudService implements HasEntityInterface {

  use EntityDataAccessorTrait;
  use HasEntityTrait;
  use StringTranslationTrait;

  /**
   * Process an entity hook.
   *
   * In addition to core, we add a combo hook fired for both insert and update:
   * - save
   * - save__BUNDLE.
   *
   * @param string $hook
   *   The hook to be fired, e.g. 'save', 'presave', etc.
   *
   * @return array
   *   The hook method return value.
   *
   * @throws \Drupal\loft_core\Entity\MissingRequiredEntityException
   *   IF the entity has not been set.
   */
  public function hook(string $hook) {
    $return = [];
    list(, , $bundle) = $this->requireEntity();
    $args = func_get_args();
    $hook = array_shift($args);
    $methods = [$hook, $hook . "__" . $bundle];
    array_walk($methods, function (&$item) {
      $item = str_replace('-', '_', $item);
    });
    if ($hook === 'access') {
      $operation = array_shift($args);
      $methods[] = $operation . '_' . $hook;
      $methods[] = $operation . '_' . $hook . "__" . $bundle;
    }
    if (in_array($hook, ['insert', 'update'])) {
      $methods[] = 'save';
      $methods[] = 'save__' . $bundle;
    }

    foreach ($methods as $method) {
      if (method_exists($this, $method)) {
        $return[$method] = call_user_func_array([
          $this,
          $method,
        ], $args);
      }
    }

    return $return;
  }

}
