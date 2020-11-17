# Permalink or "Copy link" tab

For users with the correct permissions two tabs will be added to node pages, which when clicked copies a canonical link to the clipboard.  The first tab _Copy link_ copies a canonical internal link (no domain), and should be used for page to page linking within the website.  The second tab _Share link_ should be used links to embed in emails, social sharing, etc; outside of the website context, because it contains an absolute URL.

## Hide/show tabs by role

Toggle the permissions as necessary to hide/show one or both tabs:
    
    * loft_core access local link tab (Copy link)
    * loft_core access permalink tab (Share link)

## Change tab label

To change the tab label implement `hook_local_task_alter`.  See _loft_core.api.php_ for an example implementation.
