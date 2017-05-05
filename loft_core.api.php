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
function HOOK_loft_core_redirect_node_BUNDLE_TYPE($node, $op)
{
    return [
        'node/' . BLOG_PAGE_NID,
        ['fragment' => 'm[]=modal,blog_entry__' . $node->nid],
    ];
}

/**
 * Implements hook_loft_core_code_release_info().
 *
 * @return array
 *   Each element key is a unique feature key/tag.  Each element is an array with:
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
