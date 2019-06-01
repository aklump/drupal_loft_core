<?php

/**
 * @file
 * Provides extra form functionality.
 */

use AKlump\LoftLib\Code\Arrays;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\loft_core\Redirect;

/**
 * Implements hook_form_BASE_FORM_ID_alter().
 *
 * Make sure we don't redirect admins after editing a "redirected" node.
 *
 * @param array $form
 *   The form definition.
 * @param Drupal\Core\Form\FormStateInterface $form_state
 *   The state of the form.
 * @param string $form_id
 *   The form id.
 *
 * @d8
 */
function loft_core_form_node_form_alter(array &$form, FormStateInterface $form_state, string $form_id) {
  $node = $form_state->getFormObject()->getEntity();
  if (Redirect::isNodeRedirected($node, 'view')) {
    $form_state->set('loft_core.redirect_form', TRUE);
    $form['actions']['submit']['#submit'][] = '_loft_core_node_form_submit_redirect_handler';
  }

  // Call our special combined hook.
  $node = $form_state->getFormObject()->getEntity();
  $hooks = ['loft_core_' . $node->bundle() . '_node_form'];
  \Drupal::service('module_handler')
    ->alter($hooks, $form, $form_state, $form_id);
}

/**
 * Handle a form reload on save of redirected node.
 *
 * If a node is being edited and it's been redirected by our hooks, then we
 * want to reload the edit form on save.
 *
 * @param array $form
 *   The form definition.
 * @param Drupal\Core\Form\FormStateInterface $form_state
 *   The state of the form.
 *
 * @d8
 */
function _loft_core_node_form_submit_redirect_handler(array $form, FormStateInterface $form_state) {
  if ($form_state->get('loft_core.redirect_form')) {
    $form_state->setRedirect('entity.node.edit_form', ['node' => $form_state->get('nid')]);
  }
}

/**
 * Adds a tabindex to a form element.
 *
 * Near the bottom of the form do something like this:
 *
 * @code
 *   $tabindex = 100;
 *   loft_core_form_tabindex($form['title'], $tabindex);
 *   loft_core_form_tabindex($form['actions']['create'], $tabindex);
 *   loft_core_form_tabindex($form['actions']['cancel'], $tabindex);
 * @endcode
 *
 * @param array &$element
 *   The form element to add to.
 * @param int $index
 *   The next tabindex, will automatically be incremented.
 * @param string $key
 *   What is the array key to add to? Defaults to #attributes.
 */
function loft_core_form_tabindex(array &$element, &$index, $key = '#attributes') {
  $element[$key] = $element[$key] ?? [];
  if ($element[$key] instanceof Attribute) {
    $element[$key]->setAttribute('tabindex', $index++);
  }
  else {
    $element[$key]['tabindex'] = $index++;
  }
}

/**
 * Disable an array of form elements with a consistent message.
 *
 * @code
 * $paths = array();
 * $paths[] = 'field_description.und.0';
 * $paths[] = 'field_keywords.und';
 * loft_core_form_disable_elements($form,
 * $paths);
 * @endcode
 *
 * @param array &$form
 *   The form definition.
 * @param array $paths
 *   An array of paths to be used by data_api().
 * @param string $module_name
 *   This will be used in the default message.
 * @param null $message
 *   If the default message is not sufficient, enter a custom message here.
 */
function loft_core_form_disable_elements(array &$form, array $paths, $module_name = NULL, $message = NULL) {
  $g = data_api();
  if (is_null($message)) {
    $module_name = empty($module_name) ? 'Loft Core' : $module_name;
    $message = t('This field is controlled by the %module module and cannot be modified in the UI.',
      array('%module' => $module_name));
  }
  foreach ($paths as $path) {
    if ($g->get($form, $path)) {
      $type = $g->get($form, "$path.#type");
      $g->set($form, "$path.#required", FALSE);
      $g->set($form, "$path.#disabled", TRUE);

      switch ($type) {
        case 'container':
          $message_path = "$path.#suffix";
          break;

        default:
          $message_path = "$path.#description";
          break;
      }
      $g->set($form, $message_path, $message);
    }
  }
}

/**
 * Hide form elements by adding #access = false.
 *
 * @param array $form
 *   The FAPI form definition.
 * @param array $paths
 *   An array of paths for data_api pointing to the form element to hide.
 */
function loft_core_form_hide_elements(array &$form, array $paths) {
  $g = data_api();
  foreach ($paths as $path) {
    if ($g->get($form,
      $path)
    ) {
      $g->onlyIfHas($form, "$path.#required")->call(function () {
        return FALSE;
      })->set($form);
      $g->set($form,
        "$path.#access",
        FALSE);
    }
  }
}

/**
 * Require form elements by adding #required = true.
 *
 * @param array $form
 *   The FAPI form definition.
 * @param array $paths
 *   An array of paths for data_api pointing to the form element to require.
 */
function loft_core_form_require_elements(array &$form, array $paths) {
  $g = data_api();
  foreach ($paths as $path) {
    if ($g->get($form, $path)) {
      $g->set($form, "$path.#required", TRUE);
    }
  }
}

/**
 * Do not require form elements by adding #required = false.
 *
 * @param array $form
 *   The FAPI form definition.
 * @param array $paths
 *   An array of paths for data_api pointing to the form element to make
 *   optional.
 */
function loft_core_form_optional_elements(array &$form, array $paths) {
  $g = data_api();
  foreach ($paths as $path) {
    if ($g->get($form, $path)) {
      $g->set($form, "$path.#required", FALSE);
    }
  }
}

/**
 * Set the form states on elements for a given condition
 *
 * @param array $form
 *   The FAPI form definition.
 * @param array $paths
 *   An array of paths for data_api pointing to the form element to make
 *   optional.
 */
function loft_core_form_add_state_conditions(array &$form, array $paths, string $state, array $condition) {
  $g = data_api();
  foreach ($paths as $path) {
    $path = is_string($path) ? explode('.', $path) : $path;
    if ($g->get($form, $path)) {
      $path[] = '#states';
      $path[] = $state;
      $existing_conditions = $g->get($form, $path, []);
      $existing_conditions[][] = $condition;
      $g->set($form, $path, $existing_conditions);
    }
  }
}

/**
 * Get form during AJAX callback response.
 *
 * Use this to get a form during an ajax response; it suppressed the $_POST so
 * the form doesn't think it's already submitted.
 *
 * @param string $form_id
 *   The id of the form to get.
 *
 * @return array
 *   The FAPI form definition.
 */
function loft_core_get_form_during_ajaxing($form_id) {
  $stash = $_POST;
  $_POST = array();
  $form = \Drupal::formBuilder()->getForm($form_id);
  $_POST = $stash;

  return $form;
}

/**
 * Implements hook_form_alter().
 *
 * Add an additional configuration variable for defining an email from address
 * for when a user is cancelled.
 *
 * @see loft_core_mail_alter
 *
 * @d8
 */
function loft_core_form_user_admin_settings_alter(&$form, &$form_state, $form_id) {
  Arrays::insertAfterKey($form, 'mail_notification_address', [
    'loft_core_mail_registration_from' => [
      '#title' => t('Registration email address'),
      '#type' => 'email',
      '#description' => t('If supplied, this email will be used as the from address for registration emails only, instead of the default.', array(
        ':url' => Url::fromRoute('system.site_information_settings')
          ->toString(),
      )),
      '#default_value' => \Drupal::config('loft_core.settings')
        ->get('mail_registration_from'),
    ],
  ]);

  $form['#submit'][] = 'loft_core_form_user_admin_settings_submit';
}

/**
 * Form submission handler for user_admin_settings().
 *
 * @see loft_core_form_user_admin_settings_alter()
 *
 * @d8
 */
function loft_core_form_user_admin_settings_submit($form, FormStateInterface $form_state) {
  \Drupal::configFactory()->getEditable('loft_core.settings')
    ->set('mail_registration_from', $form_state->getValue('loft_core_mail_registration_from'))
    ->save();
}
