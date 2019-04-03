<?php

/**
 * @file
 * Defines the API interfaces for loft_core module.
 */

use Drupal\Core\Url;

/**
 * Implements HOOK_loft_core_redirect_node_BUNDLE_TYPE()_view.
 *
 * This hook allows you to easily alter what happens when a user visits the
 * canonical path of a given node bundle.
 *
 * @param object $node
 *   The node object being looked at.
 *
 * @return string|array|int|\Drupal\Core\Url
 *   - A string response should begin with /,#,? and will be run through
 *   Url::fromUserInput(), then redirected.
 *   - An instance of Drupal\Core\Url is taken as a redirect.
 *   - To indicate a redirect code other than 301, return an array where:
 *     - The first value is one of the above
 *     - The second value is an integer with the response code.
 *   - Throw exceptions for 403 and 404.
 *   - Return an empty value the default core action will occur, e.g., no
 *   redirection will take place.
 *
 * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
 *   To return a 404 page to the user.
 * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
 *   To return a 403 page to the user.
 */
function HOOK_loft_core_redirect_node_BUNDLE_TYPE_view($node) {

  return '<front>';

  return '/node/' . BLOG_PAGE_NID . '#m[]=modal,blog_entry__' . $node->nid;

  return '/node/4384';

  return ['/node/4384', 302];

  return Url::fromUri('entity:node/4384');

  return [Url::fromUri('entity:node/4384'), 302];

  throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();

  throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException();
}

/**
 * Implements hook_loft_core_redirect_node_BUNDLE_TYPE_edit().
 */
function HOOK_loft_core_redirect_node_BUNDLE_TYPE_edit($node) {
  // Only allow admins to edit, otherwise deny access.  This overrides the
  // normal access check of node_access('update'...
  if (!user_is_admin) {
    throw new AccessDeniedHttpException();
  }
}

/**
 * Implements hook_loft_core_redirect_node_BUNDLE_TYPE_delete().
 */
function HOOK_loft_core_redirect_node_BUNDLE_TYPE_delete($node) {

}

/**
 * Create a node view page per bundle.
 *
 * Include this function and when a node of BUNDLE_TYPE is viewed, this
 * function will be called instead of the default node_page_view.
 *
 * No messing with hook_menu is needed, just create the function.
 *
 * @param $node
 *
 * @return array
 */
function HOOK_loft_core_node_BUNDLE_TYPE_page($node) {
  return node_page_view($node);
}

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
