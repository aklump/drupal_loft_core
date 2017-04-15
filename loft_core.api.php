<?php

/**
 * Implements HOOK_loft_core_redirect_node_BUNDLE_TYPE().
 *
 * This hook allows your module to register redirects based on node bundles.
 * This hook is fired when the menu caches are cleared as part of
 * HOOK_menu_alter.  So be sure to clear you menu cache after making changes.
 *
 * @param object $data        An object with (limited) node info you may
 *                            need for building your redirect.
 *                            - nid
 *                            - title
 * @param array  &$item       You may alter the item itself for advanced use.
 *                            In this case the return value will have no
 *                            affect IF you change the page_callback from
 *                            drupal_goto.  If you leave that alone--that is
 *                            'drupal_goto'--then the return value will become
 *                            the page_arguments of the item.  By setting $item
 *                            to null, no menu item will be created for this
 *                            NID only and the normal routing will remain.
 *
 * @return string|array|int   The return value should be the arguments for
 *                            drupal_goto.  If you do not need the $options
 *                            array, you may simply return a string, which is
 *                            the path. Unless you specify a redirect code, 301
 *                            will be used.  TAKE NOTE THIS IS NOT THE DEFAULT
 *                            FOR drupal_goto.  The value you return here
 *                            becomes the page_arguments of the menu item.  You
 *                            may also return MENU_ACCESS_DENIED or
 *                            MENU_NOT_FOUND if you wish and the appropriate
 *                            action will be taken when the url is visited,
 *                            again if $items is null, the return value is
 *                            ignored.
 */
function HOOK_loft_core_redirect_node_BUNDLE_TYPE($data, array &$item)
{
    return [
        'node/' . BLOG_PAGE_NID,
        ['fragment' => 'm[]=modal,blog_entry__' . $data->nid],
    ];
}

/**
 * Implements HOOK_query_TAG_alter().
 *
 * This example shows how to augment the data for promo_banner node types,
 * which is available to the redirect hook.  See below.  The query is tagged
 * with a tag matching the name of the hook.
 *
 * You SHOULD most likely also implement
 * HOOK_loft_core_redirect_needs_update_alter() to make sure the redirects get
 * updated when nodes change.
 */
function HOOK_query_loft_core_redirect_node_promo_banner_alter(QueryAlterableInterface $query)
{
    // Give additional field information to work with in the redirect hook.
    $query->join('field_data_field_promo_banner_url', 'u', 'n.nid = u.entity_id');
    $query->addField('u', 'field_promo_banner_url_url', 'url');
}

/**
 * Implements HOOK_loft_core_redirect_node_BUNDLE_TYPE().
 *
 * Notice how in this example we have an extra property on $data.  This is
 * because we used a query alter hook to join a table and add that field as
 * seen above.  This is how you can get extra information to work with.
 */
function HOOK_loft_core_redirect_node_promo_banner($data, &$item)
{
    return !empty($data->url) ? $data->url : MENU_ACCESS_DENIED;
}

/**
 * Implements hook_loft_core_redirect_node_has_changed_alter().
 *
 * Allow modules to tell us when a node changes, if the redirect needs to be
 * rebuilt.  We do this because the step off rebuilding the menu is expensive
 * and we don't want to assume one way or the other.
 *
 * Notice there is only one hook for ALL bundle types; unlike the other hooks.
 *
 * @see loft_core_node_update().
 */
function HOOK_loft_core_redirect_needs_update_alter(&$needs_update, $bundle, $before, $after)
{
    $n = data_api('node');
    switch ($bundle) {
        case 'alert':
        case 'promo_banner':

            // For these two node types we will detect if the url changes, if so, the redirects need to be recalculated.
            $key = 'field_promo_banner_url.0.url';
            $needs_update = $n->get($before, $key) !== $n->get($after, $key);
            break;
    }
}
