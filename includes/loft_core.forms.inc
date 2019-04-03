<?php

/**
 * @file
 * Provides extra form functionality.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\loft_core\Attribute;
use Drupal\loft_core\Redirect;

/**
 * Implements hook_form_alter().
 *
 * Make sure we don't redirect admins after editing a "redirected" node.
 *
 * @param array $form
 *   The form definition.
 * @param Drupal\Core\Form\FormStateInterface $form_state
 *   The state of the form.
 *
 * @d8
 */
function loft_core_form_node_form_alter(array &$form, FormStateInterface $form_state) {
  $node = $form_state->getFormObject()->getEntity();
  if (Redirect::isNodeRedirected($node, 'view')) {
    $form_state->set('loft_core.redirect_form', TRUE);
    $form['actions']['submit']['#submit'][] = '_loft_core_node_form_submit_redirect_handler';
  }
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
  $g = data_api();
  $g->ensure($element, $key, array());
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
 *   An array of paths for data_api pointing to the form elment to hide.
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
 */
function loft_core_form_user_admin_settings_alter(&$form, &$form_state, $form_id) {
  // @FIXME
  // Could not extract the default value because it is either indeterminate, or
  // not scalar. You'll need to provide a default value in
  // config/install/loft_core.settings.yml and config/schema/loft_core.schema.yml.
  $form['registration_cancellation']['loft_core_mail_registration_from'] = array(
    '#weight' => -1,
    '#type' => \Drupal::moduleHandler()
      ->moduleExists('emailfield') ? 'emailfield' : 'textfield',
    '#title' => t('Send registration emails from'),
    '#description' => t('This value will override the site\'s <a href="!url">default <em>From</em> address</a> for registration emails only.', array(
      '!url' => \Drupal\Core\Url::fromRoute('system.site_information_settings'),
    )),
    '#default_value' => \Drupal::config('loft_core.settings')
      ->get('loft_core_mail_registration_from'),
  );
}
