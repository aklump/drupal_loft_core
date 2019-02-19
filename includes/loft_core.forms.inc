<?php
/**
 * Provides form hooks to implemented by loft_core.
 */

use Drupal\loft_core\Redirect;
use Drupal\loft_core\Attribute;

/**
 * Implements hook_form_alter().
 *
 * Make sure we don't redirect admins after editing a "redirected" node.
 */
function loft_core_form_node_form_alter(&$form, &$form_state) {
  if (Redirect::isNodeRedirected($form_state['node'], 'view')) {
    $form['actions']['submit']['#submit'][] = 'loft_core_node_form_submit_redirect_handler';
  }
}

/**
 * Redirect a node form to the edit page,
 * no the view page as normal.
 *
 * @param $form
 * @param $form_state
 */
function loft_core_node_form_submit_redirect_handler($form, &$form_state) {
  if (!empty($form_state['redirect']) && $form_state['nid']) {
    $form_state['redirect'] = node_access('update',
      $form_state['nid']) ? 'node/' . $form_state['nid'] . '/edit' : '<front>';
  }
}

/**
 * Adds a tabindex to a form element
 *
 * @param array  &$element The form element to add to.
 * @param int    $index    The next tabindex, will automatically be incremented.
 * @param string $key      What is the array key to add to? Defaults to
 *                         #attributes.
 *
 * @return int The next tabindex available.
 *
 * Near the bottom of the form do something like this
 * @code
 *   $tabindex = 100;
 *   loft_core_form_tabindex($form['title'], $tabindex);
 *   loft_core_form_tabindex($form['actions']['create'], $tabindex);
 *   loft_core_form_tabindex($form['actions']['cancel'], $tabindex);
 * @endcode
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
 * @param array  &$form
 * @param array  $paths       An array of paths to be used by data_api().
 * @param string $module_name This will be used in the default message.
 * @param null   $message     If the default message is not sufficient,
 *                            enter a
 *                            custom message here.
 */
function loft_core_form_disable_elements(&$form, $paths, $module_name = NULL, $message = NULL) {
  $g = data_api();
  if (is_null($message)) {
    $module_name = empty($module_name) ? 'Loft Core' : $module_name;
    $message = t('This field is controlled by the %module module and cannot be modified in the UI.',
      array('%module' => $module_name));
  }
  foreach ($paths as $path) {
    if ($g->get($form,
      $path)
    ) {
      $type = $g->get($form,
        "$path.#type");
      $g->set($form,
        "$path.#required",
        FALSE);
      $g->set($form,
        "$path.#disabled",
        TRUE);
      switch ($type) {
        case 'container':
          $message_path = "$path.#suffix";
          break;
        default:
          $message_path = "$path.#description";
          break;
      }
      $g->set($form,
        $message_path,
        $message);
    }
  }
}

function loft_core_form_hide_elements(&$form, $paths) {
  $g = data_api();
  foreach ($paths as $path) {
    if ($g->get($form,
      $path)
    ) {
      $g->onlyIfHas($form,
        "$path.#required")->call(function () {
        return FALSE;
      })->set($form);
      $g->set($form,
        "$path.#access",
        FALSE);
    }
  }
}

/**
 * Use this to get a form during an ajax response; it suppressed the $_POST so
 * the form doesn't think it's already submitted.
 *
 * @param string $form_id
 *
 * @return array|mixed
 */
function loft_core_get_form_during_ajaxing($form_id) {
  $stash = $_POST;
  $_POST = array();
  $form = drupal_get_form($form_id);
  $_POST = $stash;

  return $form;
}

/**
 * Implements hook_form_alter().
 */
function loft_core_form_user_admin_settings_alter(&$form, &$form_state, $form_id) {
  $form['registration_cancellation']['loft_core_mail_registration_from'] = array(
    '#weight' => -1,
    '#type' => module_exists('emailfield') ? 'emailfield' : 'textfield',
    '#title' => t('Send registration emails from'),
    '#description' => t('This value will override the site\'s <a href="!url">default <em>From</em> address</a> for registration emails only.', array(
      '!url' => url('admin/config/system/site-information'),
    )),
    '#default_value' => variable_get('loft_core_mail_registration_from', variable_get('site_mail')),
  );
}
