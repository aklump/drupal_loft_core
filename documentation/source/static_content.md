# Stream wrapper "static-content://"

In some cases you may want to provide static content to your website, which is source controlled; not publicly accessible; is programatically appended to content; not editable by the CMS.  For all of these reasons the `private://` and `public://` stream wrappers will not suffice.  And example use case is your sites "Privacy Policy", with HTML that is too detailed to simply drop into a node body field.  Also in this case you may want to protect it from being altered except by your developers.

So then, let's use the stream wrapper `static-content://privacy-policy.html`.

By default this points to the following directory relative to web root: _../private/default/content_.  But this can be changed doing something like so in _settings.php_:

    $conf['files_static_content_path'] = '../private/cms'

## Permission & Access

The stream wrapper creates an URL like _http://website.com/system/content/privacy-policy.html_, however you must grant user the permission _Access static content_ for this to be allowed.

## How to Use

Here's an example of how you might use this in your code:

    <?php
    ['#markup' => file_get_contents('static-content://privacy-policy.html')];
