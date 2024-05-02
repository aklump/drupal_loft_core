<!--
id: trackjs
tags: ''
-->

# Add TrackJS.com to your website

1. Sign up for an account at http://www.trackjs.com
2. Obtain your token and add it to `settings.php`

        $conf['loft_core_trackjs_token'] = '5302679c8b624e0395a6a6da5b1199d6';
        
3. Add the following snippet in `html.tpl.php` in your theme before `$scripts` output.

        <?= $loft_core_tracking ?>
        <?= $scripts; ?>

4. This will only be present when `DRUPAL_ENV` is `prod`.

## Config and Metadata

See `HOOK_loft_core_trackjs_alter()` for more info.
