<?php

namespace Drupal\loft_core\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStoreFactory;

/**
 * Defines a service to protect certain entities from being deleted.
 *
 * Protected entities are those on which the app depends to function correctly,
 * certain pages, for example must be present for an app to run, maybe a
 * privacy page, or another page used functionally by the app.  These should
 * never be deleted or they would break the app's functionality.
 *
 * This service provides a mechanism to define such entities and takes steps to
 * hide delete UI for those protected entities.
 *
 * Quick start:
 * - In settings.php add the following line, which defines the constant prefix
 * to indicate how protected entities will be defined.
 *
 *    $config['loft_core.entity_protection']['prefix'] = 'SE_CORE_';
 *
 * - Define one or more protected entities using the following pattern:
 * "{prefix}{entity_type_id}_ID_*", e.g.,
 *
 *    define('SE_CORE_COMMERCE_PRODUCT_ID_COMMITTEES', 17);
 *
 * - You may use a shorthand method for nodes, "{prefix}NID_*",  e.g.,
 *
 *    define('SE_CORE_NID_COMMITTEES', 297);
 *
 * - Add this to your module's hook_form_alter:
 *
 *      \Drupal::service('loft_core.entity_protection')
 *        ->handleForm($form, $form_state, $form_id);
 *
 * - Add this to your module's hook_entity_predelete:
 *
 *     \Drupal::service('loft_core.entity_protection')
 *        ->handlePreDelete($entity);
 */
class EntityProtectionService {

  use StringTranslationTrait;
  use LoggerChannelTrait;

  static private $entities;

  private $config;

  /**
   * EntityProtectionService constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   * @param \Drupal\Core\Session\AccountInterface $current_user
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    PrivateTempStoreFactory $temp_store_factory,
    AccountInterface $current_user
  ) {
    $this->config = $config_factory->get('loft_core.entity_protection');
    $this->tempStore = $temp_store_factory->get('entity_delete_multiple_confirm');
    $this->currentUser = $current_user;
  }

  /**
   * Detect all protected entities by scanning defined constants.
   *
   * @return array
   *   An array of protected entities.  Each key is an entity type id, each
   *   value is an array of protected ids of that entity type.
   */
  protected function getProtectedEntities() {
    if (empty(self::$entities)) {
      self::$entities = [];
      foreach (get_defined_constants() as $name => $value) {
        $prefix = $this->config->get('prefix');
        if (strpos($name, $prefix) !== 0) {
          continue;
        }
        $prefix = preg_quote($prefix);
        $entity_regex = '/^' . $prefix . '(.+?)_ID_/';
        $node_regex = '/^' . $prefix . 'NID_/';
        if (preg_match($entity_regex, $name, $matches)) {
          $entity_type = $matches[1];
          $value = constant($name);
          self::$entities[strtolower($entity_type)][$value] = $value;
        }
        elseif (preg_match($node_regex, $name, $matches)) {
          $value = constant($name);
          self::$entities['node'][$value] = $value;
        }
      }
    }

    return self::$entities;
  }

  /**
   * Detect if an entity is protected.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return bool
   *   True if this is a protected entity.
   */
  public function isEntityProtected(EntityInterface $entity) {
    $entities = $this->getProtectedEntities();

    return isset($entities[$entity->getEntityTypeId()][$entity->id()]);
  }

  /**
   * Detect if an entity is protected by it's type and id.
   *
   * @param string $entity_type
   * @param integer $entity_id
   *
   * @return bool
   */
  public function isEntityProtectedById($entity_type, $entity_id) {
    $entities = $this->getProtectedEntities();

    return isset($entities[$entity->getEntityTypeId()][$entity_id]);
  }

  /**
   * Detect if an entity is protected by it's type and id.
   *
   * @param string $entity_type
   * @param integer $entity_id
   *
   * @return bool
   */
  public function isNodeProtectedById($nid) {
    $entities = $this->getProtectedEntities();

    return isset($entities['node'][$nid]);
  }

  /**
   * Add this to your custom module's hook_form_alter implementation.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param $form_id
   *
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  public function handleForm(array &$form, FormStateInterface $form_state, $form_id) {
    $base_form_id = $form_state->getBuildInfo()['base_form_id'] ?? $form_id;
    $form_object = $form_state->getFormObject();
    $entity = NULL;
    $entity_type = NULL;

    if (method_exists($form_object, 'getEntity')) {
      $entity = $form_object->getEntity();
      $entity_type = $entity->getEntityTypeId();
      $bundle = $entity->bundle();
      $form_id = preg_replace("/^{$entity_type}_{$bundle}/", "{entity_type}_{bundle}", $form_id);
      $is_protected = function () use ($entity) {
        return $this->isEntityProtected($entity);
      };
    }

    switch ($form_id) {
      case 'node_delete_multiple_confirm_form':
        $storage_key = $this->currentUser->id() . ':node';
        $filtered_items = array_filter($this->tempStore->get($storage_key), function ($nid) {
          return !$this->isNodeProtectedById($nid);
        }, ARRAY_FILTER_USE_KEY);
        $this->tempStore->set($storage_key, $filtered_items);
        $original = count($form['entities']['#items']);
        $form['entities']['#items'] = array_filter($form['entities']['#items'], function ($key) {
          list($nid) = explode(':', $key);

          return !$this->isNodeProtectedById($nid);
        }, ARRAY_FILTER_USE_KEY);
        if (($new_count = count($form['entities']['#items'])) !== $original) {
          \Drupal::messenger()
            ->addWarning("One or more protected entities have been removed from your selection.  They may not be deleted.");
        }
        if ($new_count === 0) {
          $form['actions']['submit']['#access'] = FALSE;
          $form['description']['#access'] = FALSE;
        }
        break;

      case '{entity_type}_{bundle}_edit_form':
        if (isset($form['actions']['delete']) && $is_protected()) {
          $form['actions']['delete']['#access'] = FALSE;
        }
        break;

      case '{entity_type}_{bundle}_delete_form':
        if ($is_protected()) {
          \Drupal::messenger()
            ->addWarning("This content is protected and may not be deleted.");
          $form['actions']['submit']['#access'] = FALSE;
          $form['description']['#access'] = FALSE;
        }
        break;

    }
  }

  /**
   * Add this to you an implementation of hook_entity_predelete.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   */
  public function handlePreDelete(EntityInterface $entity) {
    if ($this->isEntityProtected($entity)) {
      throw new \RuntimeException(sprintf("A protected entity may not be deleted; entity \"%s:%d\" is registered as a protected entity; see loft_core module for more information about protected entities", $entity->getEntityTypeId(), $entity->id()));
    }
  }

}
