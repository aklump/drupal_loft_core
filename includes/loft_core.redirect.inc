<?php
use Drupal\loft_core\Redirect;

/**
 * @file Supporting functions for the redirect features.
 */

/**
 * Implements hook_node_insert().
 */
function loft_core_node_insert($node)
{
    // When a new node that needs a redirection is created, we need to rebuild the menu.
    if (Redirect::isNodeRedirected($node)) {
        variable_set('menu_rebuild_needed', true);
    }
}

/**
 * Implements hook_node_update().
 */
function loft_core_node_update($node)
{
    if (Redirect::isNodeRedirected($node) && Redirect::detectNodeChange($node)) {
        variable_set('menu_rebuild_needed', true);
    }
}

/**
 * Implements hook_form_alter().
 *
 * Make sure we don't redirect admins after editing a "redirected" node.
 */
function loft_core_form_node_form_alter(&$form, &$form_state)
{
    if (Redirect::isNodeRedirected($form_state['node'])) {
        $form['actions']['submit']['#submit'][] = 'loft_core_node_form_submit_redirect_handler';
    }
}

/**
 * Redirect a node form to the edit page, no the view page as normal.
 *
 * @param $form
 * @param $form_state
 */
function loft_core_node_form_submit_redirect_handler($form, &$form_state)
{
    if (!empty($form_state['redirect']) && $form_state['nid']) {
        $form_state['redirect'] = node_access('update', $form_state['nid']) ? 'node/' . $form_state['nid'] . '/edit' : '<front>';
    }
}

/**
 * Implements hook_menu_alter().
 *
 * @param array $items
 */
function loft_core_menu_alter(array &$items)
{
    // Fire the hooks for redirects based on node type.
    if (isset($items['node/%node'])) {
        foreach (Redirect::getSupportedBundles() as $bundle) {
            $obj = new Redirect($bundle, $items['node/%node']);
            $items += $obj->getBundleRedirects();
        }
    }
}
