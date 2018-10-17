# Redirects

**In order to use this feature you must add the following to `settings.php`**

    $conf['loft_core_node_redirects'] = true;

## To redirect by node type

This module provides a clean means of redirecting node in page view by bundle.  So you could redirect all nodes in a certain bundle to a different page, mark them as access denied or even not found.  It takes the approach of injecting the redirect in node_page_view, rather than invoking something during hook_init(), which should have a lesser footprint.

    HOOK_loft_core_redirect_node_BUNDLE_TYPE_view
    HOOK_loft_core_redirect_node_BUNDLE_TYPE_edit
    HOOK_loft_core_redirect_node_BUNDLE_TYPE_delete
    HOOK_loft_core_node_BUNDLE_TYPE_page

If you set his module up to redirect say, blog nodes, then when you create a new blog, using the node edit form, instead of the normal behavior which takes you to the node view page, you will be taken to the node edit page instead.  Otherwise you would have an issue with getting redirected to places you didn't intend, and if the redirect was to another site, then you'd really be confused as an admin.
