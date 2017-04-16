# Redirects

## To redirect by node type

This module provides a clean means of redirecting node in page view by bundle.  So you could redirect all nodes in a certain bundle to a different page, mark them as access denied or even not found.  It takes the approach of creating a menu item for every redirect, rather than invoking something during hook_init().

see `HOOK_loft_core_redirect_node_BUNDLE_TYPE()`.

If you set his module up to redirect say, blog nodes, then when you create a new blog, using the node edit form, instead of the normal behavior which takes you to the node view page, you will be taken to the node edit page instead.  Otherwise you would have an issue with getting redirected to places you didn't intend, and if the redirect was to another site, then you'd really be confused as an admin.
