<?php
/**
 * Implements hook_loft_core_users_robotrap_access().
 *
 * Return true if the user should NOT be trapped.  The last module to implement
 * this will override all previous.
 */
function HOOK_loft_core_users_robotrap_access($context) {
  return user_is_admin($context['account']);
}

/**
 * Implements hook_loft_core_users_robotrap_goto_alter().
 *
 * Alter the destination of the path used when trapping users.  Use $context
 * for additional information as needed.
 */
function HOOK_loft_core_users_robotrap_goto_alter(&$path, &$options, $context) {
  if ($path === 'contact') {
    $options = array('query' => array('cid' => 6));
  }
}
