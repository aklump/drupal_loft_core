# Stream wrapper "static-content://"

In some cases you may want to provide static content to your website, which is source controlled; not publicly accessible; is programatically appended to content; not editable by the CMS.  For all of these reasons the `private://` and `public://` stream wrappers will not suffice.  And example use case is your sites "Privacy Policy", with HTML that is too detailed to simply drop into a node body field.  Also in this case you may want to protect it from being altered except by your developers.

So then, let's use the stream wrapper `static-content://privacy-policy.html`.

By default this points to the following directory relative to web root: _../private/default/content_.  But this can be changed doing something like so in _settings.php_:

    $conf['files_static_content_path'] = '../private/cms'

## Permission & Access

The stream wrapper creates an URL like _/system/content/privacy-policy.html_, however you must grant user the permission _Access static content_ for this to be allowed.  If they have access they can see the raw contents of your file.

## How to Use

Here's an example of how you might use this in a render array

    <?php
    ['#markup' => file_get_contents('static-content://privacy-policy.html')];

### Replace a node's body with static content

It's possible you want to use static content for a node body.

    <?php
    function my_module_node_load($nodes, $types) {
      foreach ($nodes as $node) {
        if ($node->nid === 123) {
          $node->body['und'][0]['body'] = file_get_contents('static-content://privacy-policy.html');
          
          // Use a format that doesn't mess with your static content here.
          $node->body['und'][0]['format'] = 'raw_html';
        }
      }
    }

You will also want to update your admin UI to lock out editing; something like the following...

    <?php
    
    function my_module_form_page_node_form_alter(&$form, &$form_state, $form_id) {
      $nid = $form['#node']->nid;
      if ($nid == 123) {
        // We hide the forms for these two nodes because they use the
        // static-content:// wrapper to obtain their contents from HTML file.
        loft_core_form_disable_elements($form, ['body']);
      }
    }
