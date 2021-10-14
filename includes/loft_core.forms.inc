<?php

/**
 * @file
 * Provides extra form functionality.
 */

use AKlump\LoftLib\Code\Arrays;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use \Drupal\Component\Utility\NestedArray;

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
 */
function loft_core_form_node_form_alter(array &$form, FormStateInterface $form_state, string $form_id) {
  // Call our special combined hook.
  $node = $form_state->getFormObject()->getEntity();
  $hooks = ['loft_core_' . $node->bundle() . '_node_form'];
  \Drupal::service('module_handler')
    ->alter($hooks, $form, $form_state, $form_id);
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
 *   An array of: nested array keys or dot-separated strings, e.g.
 *   ["foo.bar.baz", "do.re"] or [['foo', 'bar', 'baz'], ['do', 're']], pointing
 *   to the form elements to disable.
 * @param string $module_name
 *   This will be used in the default message.
 * @param null $message
 *   If the default message is not sufficient, enter a custom message here.
 */
function loft_core_form_disable_elements(array &$form, array $paths, $module_name = NULL, $message = NULL) {
  if (is_null($message)) {
    $module_name = empty($module_name) ? 'Loft Core' : $module_name;
    $message = t('This field is controlled by the %module module and cannot be modified in the UI.',
      array('%module' => $module_name));
  }
  foreach ($paths as $path) {
    $parents = is_array($path) ? $path : explode('.', $path);
    if (!empty(NestedArray::getValue($form, $parents))) {
      $required = boolval(NestedArray::getValue($form, array_merge($parents, ['#required'])));
      NestedArray::setValue($form, array_merge($parents, ['#required']), FALSE);
      NestedArray::setValue($form, array_merge($parents, ['#disabled']), TRUE);

      // Address fields seem to require this as well.
      NestedArray::setValue($form, array_merge($parents, [
        '#attributes',
        'disabled',
      ]), TRUE);

      $type = NestedArray::getValue($form, array_merge($parents, ['#type']));
      switch ($type) {
        case 'container':
          $message_path = array_merge($parents, ['#suffix']);
          break;

        default:
          $message_path = array_merge($parents, ['#description']);
          break;
      }
      NestedArray::setValue($form, $message_path, $message);
    }
  }
}

/**
 * Hide form elements by adding #access = false.
 *
 * @param array $form
 *   The FAPI form definition.
 * @param array $paths
 *   An array of: nested array keys or dot-separated strings, e.g.
 *   ["foo.bar.baz", "do.re"] or [['foo', 'bar', 'baz'], ['do', 're']], pointing
 *   to the form element to hide.
 */
function loft_core_form_hide_elements(array &$form, array $paths) {
  foreach ($paths as $path) {
    $parents = is_array($path) ? $path : explode('.', $path);
    if (!empty(NestedArray::getValue($form, $parents))) {
      $required = boolval(NestedArray::getValue($form, array_merge($parents, ['#required'])));
      if (!is_null($required)) {
        NestedArray::setValue($form, array_merge($parents, ['#required']), FALSE);
      }
      NestedArray::setValue($form, array_merge($parents, ['#access']), FALSE);
    }
  }
}

/**
 * Show form elements by adding #access = true.
 *
 * @param array $form
 *   The FAPI form definition.
 * @param array $paths
 *   An array of: nested array keys or dot-separated strings, e.g.
 *   ["foo.bar.baz", "do.re"] or [['foo', 'bar', 'baz'], ['do', 're']], pointing
 *   to the form element to show.
 */
function loft_core_form_show_elements(array &$form, array $paths) {
  foreach ($paths as $path) {
    $parents = is_array($path) ? $path : explode('.', $path);
    if (!empty(NestedArray::getValue($form, $parents))) {
      $required = boolval(NestedArray::getValue($form, array_merge($parents, ['#required'])));
      if (!is_null($required)) {
        NestedArray::setValue($form, array_merge($parents, ['#required']), FALSE);
      }
      NestedArray::setValue($form, array_merge($parents, ['#access']), TRUE);
    }
  }
}

/**
 * Hide form groups.
 *
 * This must be called in hook_field_group_form_process_build_alter.
 *
 * @param array $form
 * @param array $group_paths
 */
function loft_core_form_hide_groups(array &$form, array $group_paths) {
  $hidden_elements = [];
  foreach ($group_paths as $hidden_group) {
    if (isset($form['#fieldgroups'][$hidden_group])) {
      $hidden_elements = array_merge($form['#fieldgroups'][$hidden_group]->children, $hidden_elements);
      $form['#fieldgroups'][$hidden_group]->children = [];
      $hidden_elements[] = $hidden_group;
    }
  }

  loft_core_form_hide_elements($form, $hidden_elements, 'se_core');
}

/**
 * Require form elements by adding #required = true.
 *
 * @param array $form
 *   The FAPI form definition.
 * @param array $paths
 *   An array of: nested array keys or dot-separated strings, e.g.
 *   ["foo.bar.baz", "do.re"] or [['foo', 'bar', 'baz'], ['do', 're']], pointing
 *   to the form element to require.
 */
function loft_core_form_require_elements(array &$form, array $paths) {
  foreach ($paths as $path) {
    $parents = is_array($path) ? $path : explode('.', $path);
    if (NestedArray::getValue($form, $parents)) {
      NestedArray::setValue($form, array_merge($parents, ['#required']), TRUE);
    }
  }
}

/**
 * Do not require form elements by adding #required = false.
 *
 * @param array $form
 *   The FAPI form definition.
 * @param array $paths
 *   An array of: nested array keys or dot-separated strings, e.g.
 *   ["foo.bar.baz", "do.re"] or [['foo', 'bar', 'baz'], ['do', 're']], pointing
 *   to the form element to make optional.
 */
function loft_core_form_optional_elements(array &$form, array $paths) {
  foreach ($paths as $path) {
    $parents = is_array($path) ? $path : explode('.', $path);
    if (NestedArray::getValue($form, $parents)) {
      NestedArray::setValue($form, array_merge($parents, ['#required']), FALSE);
    }
  }
}

/**
 * Set the form states on elements for a given condition
 *
 * @param array $form
 *   The FAPI form definition.
 * @param array $paths
 *   An array of: nested array keys or dot-separated strings, e.g.
 *   ["foo.bar.baz", "do.re"] or [['foo', 'bar', 'baz'], ['do', 're']], pointing
 *   to the form element to add state to.
 */
function loft_core_form_add_state_conditions(array &$form, array $paths, string $state, array $condition) {
  foreach ($paths as $path) {
    $parents = is_array($path) ? $path : explode('.', $path);
    if (NestedArray::getValue($form, $parents)) {
      $parents[] = '#states';
      $parents[] = $state;
      $existing_conditions = NestedArray::getValue($form, $parents) ?? [];
      $existing_conditions[][] = $condition;
      NestedArray::setValue($form, $parents, $existing_conditions);
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
 * Add test classes to a paragraphs element.
 *
 * @code
 *   // Where field_components is a paragraph reference field pointing to
 *   multiple paragraph types.
 *   loft_core_paragraphs_element_add_test_classes($form, [
 *     'field_components',
 *   ]);
 *   loft_core_element_add_test_classes($form, [
 *     'actions.submit as create_board',
 *   ]);
 * @endcode
 *
 * @param array $form
 * @param array $paths
 */
function loft_core_paragraphs_element_add_test_classes(array &$form, array $paths): void {
  $final_paths = [];
  foreach ($paths as $field_name) {
    if (!is_string($field_name)) {
      throw new \InvalidArgumentException("Each element of $field_name must be a string, which is a fieldname");
    }
    if (empty($form[$field_name]['widget']['add_more'])) {
      throw new \InvalidArgumentException("The $field_name element does not have an \"add_more\" widget");
    }
    $final_paths[] = "$field_name.widget.add_more as {$field_name}_add";
    foreach (Element::children($form[$field_name]['widget']['add_more']['operations']['#links']) as $option) {
      $alias = str_replace('add_more_button_', $field_name . '_add__', $option);
      $final_paths[] = implode('.',
          [
            $field_name,
            'widget.add_more.operations.#links',
            $option,
            'title',
          ]) . ' as ' . $alias;
    }
    foreach (Element::children($form[$field_name]['widget']) as $item) {
      if ($item === 'add_more') {
        continue;
      }
      $alias = $field_name . "__item$item";
      $final_paths[] = implode('.',
          [
            $field_name,
            'widget',
            $item,
          ]) . ' as ' . $alias;
    }
    loft_core_element_add_test_classes($form, $final_paths);
  }
}
