<?php

/**
 * @file
 * Defines the API interfaces for loft_core module.
 */

use Drupal\Core\Url;
use Drupal\loft_core\Block\BlockRebuilder;

/**
 * Implements hook_loft_core_code_release_info().
 *
 * @return array
 *   Each element key is a unique feature key/tag.  Each element is an array
 *   with:
 *     - is_live bool False to disable the feature.
 */
function hook_loft_core_code_release_info() {
  return array(
    'photoshare' => array(
      'is_ready' => FALSE,
      'is_live' => FALSE,
      'description' => 'Ability to share individual photo essay photos.',
    ),
    'passhelp' => array(
      'is_ready' => FALSE,
      'is_live' => FALSE,
      'description' => 'Link during login proess to open a modal where user can request a new password',
    ),
    'facebook' => array(
      'is_ready' => FALSE,
      'is_live' => FALSE,
    ),
    'comments' => array(
      'is_ready' => FALSE,
      'is_live' => FALSE,
    ),
    'avatars' => array(
      'is_ready' => FALSE,
      'is_live' => FALSE,
    ),
    'blog' => array(
      'is_ready' => FALSE,
      'is_live' => FALSE,
    ),
    'wysiwyg' => array(
      'is_ready' => FALSE,
      'is_live' => FALSE,
    ),
    'tour' => array(
      'is_ready' => TRUE,
      'is_live' => FALSE,
    ),
  );
}

/**
 * Implements hook_loft_core_trackjs_alter().
 *
 * @link http://docs.trackjs.com/tracker/configuration
 * @link http://docs.trackjs.com/tracker/top-level-api
 */
function HOOK_loft_core_trackjs_alter(array &$config) {
  // Set the application.
  $config['config']['application'] = 'my_first_app';

  // Add some metadata.
  $config['metadata']['do'] = 're';
}

/**
 * Implements HOOK_loft_core_suppress_messages().
 *
 * Allow modules to suppress system messages based on regex expressions.
 *
 * @return array
 *   Keyed by message status, e.g. status, error.
 *   Each value is an array of regex expressions, that when matched causes the
 *   message to never display.
 */
function HOOK_loft_core_suppress_messages() {
  return array(
    'status' => array(
      '/^You are now logged in as/',
    ),
  );
}

/**
 * Implements HOOK_loft_core_BUNDLE_node_form_alter().
 *
 * A form alter than runs for both node ADD and node EDIT forms.
 *
 * @param array $form
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 * @param string $form_id
 */
function HOOK_loft_core_BUNDLE_node_form_alter(array &$form, \Drupal\Core\Form\FormStateInterface $form_state, string $form_id) {
  $node = $form_state->getFormObject()->getEntity();

  ...
}

/**
 * Renders a block as learn_more_card.
 */
class LearnMoreCardBlock extends BlockRebuilder {

  /**
   * {@inheritdoc}
   */
  public function rebuild($label, $content) {
    return [
      '#theme' => 'learn_more_card',
      '#attributes' => $this->getAttributes(),
      '#title' => $label,
      '#body' => $content['body'],
      '#target_href' => $this->element['#target_href'],
    ];
  }

}
