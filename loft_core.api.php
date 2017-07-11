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
 *                            Finally, if you return FALSE, no redirect will
 *                            take place.
 */
function HOOK_loft_core_redirect_node_BUNDLE_TYPE_view($node)
{
    return [
        'node/' . BLOG_PAGE_NID,
        ['fragment' => 'm[]=modal,blog_entry__' . $node->nid],
    ];
}

function HOOK_loft_core_redirect_node_BUNDLE_TYPE_edit($node)
{
    // Only allow admins to edit, otherwise deny access.  This overrides the normal access check of node_access('update'...
    return user_is_admin() ? false : MENU_ACCESS_DENIED;
}

function HOOK_loft_core_redirect_node_BUNDLE_TYPE_delete($node)
{

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
function HOOK_loft_core_node_BUNDLE_TYPE_page($node)
{
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
function hook_loft_core_code_release_info()
{
    return [
        'photoshare' => [
            'is_ready' => false,
            'is_live' => false,
            'description' => 'Ability to share individual photo essay photos.',
        ],
        'passhelp' => [
            'is_ready' => false,
            'is_live' => false,
            'description' => 'Link during login proess to open a modal where user can request a new password',
        ],
        'facebook' => [
            'is_ready' => false,
            'is_live' => false,
        ],
        'comments' => [
            'is_ready' => false,
            'is_live' => false,
        ],
        'avatars' => [
            'is_ready' => false,
            'is_live' => false,
        ],
        'blog' => [
            'is_ready' => false,
            'is_live' => false,
        ],
        'wysiwyg' => [
            'is_ready' => false,
            'is_live' => false,
        ],
        'tour' => [
            'is_ready' => true,
            'is_live' => false,
        ],
    ];
}

/**
 * Implements hook_loft_core_trackjs_alter().
 *
 * @link http://docs.trackjs.com/tracker/configuration
 * @link http://docs.trackjs.com/tracker/top-level-api
 */
function HOOK_loft_core_trackjs_alter(&$config)
{
    // Set the application.
    $config['config']['application'] = 'my_first_app';

    // Add some metadata
    $config['metadata']['do'] = 're';
}


/**
 * Implements hook_loft_core_robotrap_access().
 *
 * Return true if the user should NOT be trapped.  The last module to implement
 * this will override all previous.
 */
function HOOK_loft_core_robotrap_access($context)
{
    return user_is_admin($context['account']);
}

/**
 * Implements hook_loft_core_robotrap_goto_alter().
 *
 * Alter the destination of the path used when trapping users.  Use $context
 * for additional information as needed.
 */
function HOOK_loft_core_robotrap_goto_alter(&$path, &$options, $context)
{
    if ($path === 'contact') {
        $options = ['query' => ['cid' => 6]];
    }
}
