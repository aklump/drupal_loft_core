<?php

namespace Drupal\loft_core\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Render\Element;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TempStore\PrivateTempStore;
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
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  private $tempStore;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  private $currentUser;

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
    if (($prefix = $this->config->get('prefix')) && empty(self::$entities)) {
      self::$entities = [];
      foreach (get_defined_constants() as $name => $value) {
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

    return isset($entities[$entity_type][$entity_id]);
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
    $form_object = $form_state->getFormObject();
    $base_form_id = $form_state->getBuildInfo()['base_form_id'] ?? $form_id;
    $is_protected = function () {
      return FALSE;
    };

    if (method_exists($form_object, 'getEntity')) {
      $entity = $form_object->getEntity();
      $entity_type = $entity->getEntityTypeId();
      $bundle = $entity->bundle();
      $form_id = preg_replace("/^{$entity_type}_{$bundle}/", "{entity_type}_{bundle}", $form_id);
      $form_id = preg_replace("/^{$entity_type}/", "{entity_type}", $form_id);
      $is_protected = function () use ($entity) {
        return $this->isEntityProtected($entity);
      };
    }

    switch ($base_form_id) {
      case 'user_multiple_cancel_confirm':
        $entity_type = 'user';
        $filtered_items = Element::children($form['accounts']);
        $original_count = count($filtered_items);
        $filtered_items = array_filter($filtered_items, function ($id) use ($entity_type) {
          return !$this->isEntityProtectedById($entity_type, $id);
        });
        $form['accounts'] = array_filter($form['accounts'], function ($uid) use ($filtered_items) {
          return !is_numeric($uid) || in_array($uid, $filtered_items);
        }, ARRAY_FILTER_USE_KEY);
        $form['account']['names']['#items'] = array_filter($form['account']['names']['#items'], function ($uid) use ($filtered_items) {
          return in_array($uid, $filtered_items);
        }, ARRAY_FILTER_USE_KEY);

        if (($new_count = count($filtered_items)) !== $original_count) {
          \Drupal::messenger()
            ->addWarning("One or more protected entities have been removed from your selection.  They may not be deleted.");
        }
        if ($new_count === 0) {
          $form['actions']['submit']['#access'] = FALSE;
          $form['description']['#access'] = FALSE;
          $form['user_cancel_confirm']['#access'] = FALSE;
          $form['user_cancel_method']['#access'] = FALSE;
          $form['user_cancel_notify']['#access'] = FALSE;
        }
        break;

      case 'entity_delete_multiple_confirm_form':
        $entity_type = $form_state->getBuildInfo()['args'][0];
        $storage_key = $this->currentUser->id() . ':' . $entity_type;
        $filtered_items = array_filter($this->tempStore->get($storage_key), function ($id) use ($entity_type) {
          return !$this->isEntityProtectedById($entity_type, $id);
        }, ARRAY_FILTER_USE_KEY);
        $this->tempStore->set($storage_key, $filtered_items);
        $original_count = count($form['entities']['#items']);
        $form['entities']['#items'] = array_filter($form['entities']['#items'], function ($key) use ($entity_type) {
          list($id) = explode(':', $key);

          return !$this->isEntityProtectedById($entity_type, $id);
        }, ARRAY_FILTER_USE_KEY);
        if (($new_count = count($form['entities']['#items'])) !== $original_count) {
          \Drupal::messenger()
            ->addWarning("One or more protected entities have been removed from your selection.  They may not be deleted.");
        }
        if ($new_count === 0) {
          $form['actions']['submit']['#access'] = FALSE;
          $form['description']['#access'] = FALSE;
        }
        break;
    }

    switch ($form_id) {
      case '{entity_type}_form':
      case '{entity_type}_{bundle}_form':
      case '{entity_type}_{bundle}_edit_form':
        if (isset($form['actions']['delete']) && $is_protected()) {
          $form['actions']['delete']['#access'] = FALSE;
        }
        break;

      case '{entity_type}_cancel_form':
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
