<?php

/**
 * Implements HOOK_loft_core_redirect_node_BUNDLE_TYPE().
 *
 * This hook allows your module to register redirects based on node bundles.
 * This hook is fired when the menu caches are cleared as part of
 * HOOK_menu_alter.  So be sure to clear you menu cache after making changes.
 *
 * @param object $node The node object in question.
 *
 * @return string|array|int   The return value should be the arguments for
 *                            drupal_goto.  If you do not need the $options
 *                            array, you may simply return a string, which is
 *                            the path. Unless you specify a redirect code, 301
 *                            will be used.  TAKE NOTE THIS IS NOT THE DEFAULT
 *                            FOR drupal_goto.  You may also return
 *                            MENU_ACCESS_DENIED or MENU_NOT_FOUND if you wish
 *                            and the appropriate action will be taken.
 */
function HOOK_loft_core_redirect_node_BUNDLE_TYPE($node)
{
    return [
        'node/' . BLOG_PAGE_NID,
        ['fragment' => 'm[]=modal,blog_entry__' . $node->nid],
    ];
}
