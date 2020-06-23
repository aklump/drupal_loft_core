# Permalink or "Copy link" tab

For users with the correct permissions a tab will be added to node pages, which when clicked copies a canonical link to the clipboard.

## Absolute links

By default the copy link is relative. To make the copied value absolute add the following to _settings.php_.

    $config['loft_core.settings']['permalink_type'] = 'absolute';

## Change tab label

To change the tab label implement `hook_local_task_alter`.  See _loft_core.api.php_ for an example implementation.
